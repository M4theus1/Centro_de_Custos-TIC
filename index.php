<?php
session_start();

// ─────────────────────────────────────────────
// Segurança: regenera sessão a cada login
// ─────────────────────────────────────────────
include(__DIR__ . '/./config/config.php');

if (!$mysqli) {
    http_response_code(500);
    die(json_encode(['erro' => 'Falha na conexão com o banco de dados.']));
}

// ─────────────────────────────────────────────
// Rate limiting simples por IP (via sessão)
// ─────────────────────────────────────────────
$ip_acesso       = $_SERVER['REMOTE_ADDR'];
$navegador_acesso = $_SERVER['HTTP_USER_AGENT'];

if (!isset($_SESSION['tentativas'])) {
    $_SESSION['tentativas']    = 0;
    $_SESSION['primeiro_erro'] = 0;
}

// Reseta contador após 15 minutos
if ($_SESSION['tentativas'] > 0 && (time() - $_SESSION['primeiro_erro']) > 900) {
    $_SESSION['tentativas']    = 0;
    $_SESSION['primeiro_erro'] = 0;
}

$bloqueado       = $_SESSION['tentativas'] >= 5;
$erro_login      = '';
$sucesso_login   = false;

// ─────────────────────────────────────────────
// Processamento do formulário
// ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$bloqueado) {

    // Proteção CSRF básica
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $erro_login = 'Requisição inválida. Atualize a página e tente novamente.';
    } else {
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        $senha = trim($_POST['senha'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($senha)) {
            $erro_login = 'Preencha todos os campos corretamente.';
        } else {
            $sql  = "SELECT id, nome, senha, nivel_acesso, precisa_trocar_senha FROM usuarios WHERE email = ? LIMIT 1";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result  = $stmt->get_result();
            $usuario = $result->fetch_assoc();
            $stmt->close();

            $sucesso_db = 0;
            $usuario_id = null;

            if ($usuario && password_verify($senha, $usuario['senha'])) {
                // ── Login bem-sucedido ──────────────────
                session_regenerate_id(true);

                $_SESSION['id']          = $usuario['id'];
                $_SESSION['usuario_nome']= $usuario['nome'];
                $_SESSION['nivel_acesso']= $usuario['nivel_acesso'];
                $_SESSION['tentativas']  = 0;

                if (isset($_POST['lembrar'])) {
                    setcookie('email_salvo', $email, time() + 86400 * 30, '/', '', true, true);
                } else {
                    setcookie('email_salvo', '', time() - 3600, '/', '', true, true);
                }

                $sucesso_db  = 1;
                $usuario_id  = $usuario['id'];
                $sucesso_login = true;
            } else {
                // ── Login falhou ────────────────────────
                $_SESSION['tentativas']++;
                if ($_SESSION['tentativas'] === 1) {
                    $_SESSION['primeiro_erro'] = time();
                }
                $restantes   = 5 - $_SESSION['tentativas'];
                $erro_login  = $restantes > 0
                    ? "E-mail ou senha incorretos. Tentativas restantes: {$restantes}."
                    : 'Conta temporariamente bloqueada por excesso de tentativas. Aguarde 15 minutos.';
                $bloqueado   = $_SESSION['tentativas'] >= 5;
            }

            // ── Log de acesso ───────────────────────────
            $sql_log  = "INSERT INTO logs_acesso (usuario_id, data_acesso, sucesso, ip_acesso, navegador_acesso) VALUES (?, NOW(), ?, ?, ?)";
            $stmt_log = $mysqli->prepare($sql_log);
            $stmt_log->bind_param('iiss', $usuario_id, $sucesso_db, $ip_acesso, $navegador_acesso);
            $stmt_log->execute();
            $stmt_log->close();

            // ── Redirecionar ────────────────────────────
            if ($sucesso_login) {
                $destino = $usuario['precisa_trocar_senha']
                    ? 'trocar_senha.php'
                    : '/centro_de_custos/dashboard/painel.php';
                header("Location: {$destino}");
                exit();
            }
        }
    }
}

// Gera token CSRF para o formulário
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$email_cookie = isset($_COOKIE['email_salvo']) ? htmlspecialchars($_COOKIE['email_salvo']) : '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Centro de Custos TIC · DTEL Telecom</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

<style>
/* ─── Reset & base ─────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --bg:        #0b0f1a;
    --panel:     #111827;
    --border:    rgba(255,255,255,.07);
    --accent:    #2563eb;
    --accent-h:  #1d4ed8;
    --accent-g:  #3b82f6;
    --text:      #f1f5f9;
    --muted:     #64748b;
    --error:     #f87171;
    --success:   #34d399;
    --input-bg:  #1e293b;
    --radius:    14px;
    --shadow:    0 32px 80px rgba(0,0,0,.6);
    --glow:      0 0 60px rgba(37,99,235,.25);
}

html, body {
    height: 100%;
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    overflow: hidden;
}

/* ─── Background pattern ───────────────────── */
body::before {
    content: '';
    position: fixed; inset: 0; z-index: 0;
    background:
        radial-gradient(ellipse 80% 60% at 20% 0%,   rgba(37,99,235,.18)  0%, transparent 60%),
        radial-gradient(ellipse 60% 50% at 85% 100%,  rgba(59,130,246,.12) 0%, transparent 55%),
        repeating-linear-gradient(
            0deg,
            transparent,
            transparent 39px,
            rgba(255,255,255,.025) 39px,
            rgba(255,255,255,.025) 40px
        ),
        repeating-linear-gradient(
            90deg,
            transparent,
            transparent 39px,
            rgba(255,255,255,.025) 39px,
            rgba(255,255,255,.025) 40px
        );
}

/* ─── Layout ───────────────────────────────── */
.page {
    position: relative; z-index: 1;
    display: flex; align-items: center; justify-content: center;
    min-height: 100vh;
    padding: 24px;
}

/* ─── Card ─────────────────────────────────── */
.card {
    width: 100%; max-width: 440px;
    background: var(--panel);
    border: 1px solid var(--border);
    border-radius: calc(var(--radius) * 1.4);
    box-shadow: var(--shadow), var(--glow);
    overflow: hidden;
    animation: rise .55s cubic-bezier(.22,.68,0,1.2) both;
}

@keyframes rise {
    from { opacity: 0; transform: translateY(28px) scale(.97); }
    to   { opacity: 1; transform: none; }
}

/* ─── Header band ──────────────────────────── */
.card-header {
    padding: 36px 40px 28px;
    border-bottom: 1px solid var(--border);
    position: relative;
    overflow: hidden;
}

.card-header::after {
    content: '';
    position: absolute; bottom: -1px; left: 40px; right: 40px;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--accent), var(--accent-g), transparent);
    opacity: .6;
}

.brand-tag {
    font-family: 'Syne', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: .16em;
    text-transform: uppercase;
    color: var(--accent-g);
    margin-bottom: 10px;
    display: flex; align-items: center; gap: 8px;
}

.brand-tag::before {
    content: '';
    display: inline-block; width: 20px; height: 2px;
    background: var(--accent-g);
    border-radius: 2px;
}

.card-title {
    font-family: 'Syne', sans-serif;
    font-size: 26px; font-weight: 800;
    line-height: 1.15;
    letter-spacing: -.02em;
    color: var(--text);
}

.card-title span { color: var(--accent-g); }

.card-sub {
    margin-top: 6px;
    font-size: 14px; color: var(--muted);
    font-weight: 300;
}

/* ─── Body ─────────────────────────────────── */
.card-body { padding: 32px 40px 36px; }

/* ─── Alert ────────────────────────────────── */
.alert {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 12px 14px;
    border-radius: var(--radius);
    font-size: 13.5px; line-height: 1.5;
    margin-bottom: 22px;
    animation: shake .4s ease both;
}
.alert-error  { background: rgba(248,113,113,.1); border: 1px solid rgba(248,113,113,.25); color: var(--error); }
.alert svg    { flex-shrink: 0; margin-top: 1px; }

@keyframes shake {
    0%,100%{ transform: translateX(0); }
    20%    { transform: translateX(-5px); }
    40%    { transform: translateX(5px); }
    60%    { transform: translateX(-4px); }
    80%    { transform: translateX(4px); }
}

/* ─── Form groups ──────────────────────────── */
.form-group { margin-bottom: 20px; }

.form-label {
    display: block;
    font-size: 12.5px; font-weight: 500;
    color: var(--muted);
    letter-spacing: .05em;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.input-wrap {
    position: relative;
}

.input-wrap svg.icon {
    position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
    color: var(--muted);
    pointer-events: none;
    transition: color .2s;
}

.form-input {
    width: 100%;
    padding: 13px 14px 13px 42px;
    background: var(--input-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    font-size: 15px;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
}

.form-input::placeholder { color: #374151; }

.form-input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(37,99,235,.18);
}

.form-input:focus + svg.icon,
.input-wrap:focus-within svg.icon { color: var(--accent-g); }

/* toggle senha */
.btn-eye {
    position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer;
    color: var(--muted); transition: color .2s; padding: 2px;
    display: flex; align-items: center;
}
.btn-eye:hover { color: var(--text); }

/* ─── Row lembrar / esqueci ────────────────── */
.row-remember {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 26px;
}

.check-label {
    display: flex; align-items: center; gap: 8px;
    font-size: 13.5px; color: var(--muted);
    cursor: pointer; user-select: none;
}

.check-label input[type=checkbox] { display: none; }

.check-box {
    width: 16px; height: 16px;
    border: 1.5px solid var(--muted);
    border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    transition: background .2s, border-color .2s;
    flex-shrink: 0;
}

.check-label input:checked + .check-box {
    background: var(--accent);
    border-color: var(--accent);
}

.check-label input:checked + .check-box svg { display: block; }
.check-box svg { display: none; }

.link-forgot {
    font-size: 13px; color: var(--accent-g);
    text-decoration: none;
    transition: color .2s;
}
.link-forgot:hover { color: var(--text); }

/* ─── Submit button ────────────────────────── */
.btn-submit {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, var(--accent), var(--accent-g));
    border: none; border-radius: var(--radius);
    color: #fff;
    font-family: 'Syne', sans-serif;
    font-size: 15px; font-weight: 700;
    letter-spacing: .04em;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: opacity .2s, box-shadow .2s, transform .15s;
    box-shadow: 0 6px 24px rgba(37,99,235,.35);
    position: relative; overflow: hidden;
}

.btn-submit::after {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,.12), transparent);
    opacity: 0; transition: opacity .2s;
}

.btn-submit:hover { opacity: .92; box-shadow: 0 8px 32px rgba(37,99,235,.5); }
.btn-submit:hover::after { opacity: 1; }
.btn-submit:active { transform: scale(.98); }
.btn-submit:disabled { opacity: .5; cursor: not-allowed; }

/* ─── Spinner ──────────────────────────────── */
.spinner {
    display: none;
    width: 18px; height: 18px;
    border: 2px solid rgba(255,255,255,.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Footer ───────────────────────────────── */
.card-footer {
    padding: 16px 40px;
    border-top: 1px solid var(--border);
    font-size: 12px; color: var(--muted);
    text-align: center;
    background: rgba(0,0,0,.15);
}

/* ─── Bloqueado overlay ────────────────────── */
.blocked-msg {
    display: flex; flex-direction: column; align-items: center;
    gap: 12px; padding: 12px 0 4px; text-align: center;
    font-size: 14px; color: var(--error);
}

.blocked-msg svg { opacity: .8; }

/* ─── Responsive ───────────────────────────── */
@media (max-width: 480px) {
    .card-header, .card-body { padding-left: 24px; padding-right: 24px; }
    .card-footer { padding-left: 24px; padding-right: 24px; }
}
</style>
</head>
<body>
<div class="page">
  <div class="card">

    <!-- Header -->
    <div class="card-header">
      <p class="brand-tag">DTEL Telecom</p>
      <h1 class="card-title">Centro de <span>Custos</span> TIC</h1>
      <p class="card-sub">Acesse sua conta para continuar</p>
    </div>

    <!-- Body -->
    <div class="card-body">

      <?php if ($bloqueado): ?>
        <div class="blocked-msg">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
          </svg>
          <strong>Acesso temporariamente bloqueado</strong>
          <span>Muitas tentativas incorretas. Aguarde 15 minutos antes de tentar novamente.</span>
        </div>

      <?php else: ?>

        <?php if ($erro_login): ?>
          <div class="alert alert-error" role="alert">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r=".5" fill="currentColor"/>
            </svg>
            <?= htmlspecialchars($erro_login) ?>
          </div>
        <?php endif; ?>

        <form method="POST" id="loginForm" novalidate>
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

          <!-- E-mail -->
          <div class="form-group">
            <label class="form-label" for="email">E-mail</label>
            <div class="input-wrap">
              <input
                type="email" id="email" name="email"
                class="form-input"
                placeholder="seu@email.com.br"
                value="<?= $email_cookie ?>"
                autocomplete="email"
                required
              >
              <svg class="icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
              </svg>
            </div>
          </div>

          <!-- Senha -->
          <div class="form-group">
            <label class="form-label" for="senha">Senha</label>
            <div class="input-wrap">
              <input
                type="password" id="senha" name="senha"
                class="form-input"
                placeholder="••••••••"
                autocomplete="current-password"
                required
              >
              <svg class="icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
              <button type="button" class="btn-eye" id="toggleSenha" aria-label="Mostrar/ocultar senha">
                <svg id="eyeIcon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Lembrar / Esqueci -->
          <div class="row-remember">
            <label class="check-label">
              <input type="checkbox" name="lembrar" id="lembrar" <?= $email_cookie ? 'checked' : '' ?>>
              <span class="check-box">
                <svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke="#fff" stroke-width="2.2">
                  <polyline points="1.5,6 5,9.5 10.5,2.5"/>
                </svg>
              </span>
              Lembrar e-mail
            </label>
            <a href="/centro_de_custos/settings/user_forgot_pass.php" class="link-forgot">Esqueci minha senha</a>
          </div>

          <!-- Botão -->
          <button type="submit" class="btn-submit" id="btnSubmit">
            <span id="btnText">Entrar</span>
            <svg id="btnArrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="5" y1="12" x2="19" y2="12"/>
              <polyline points="12,5 19,12 12,19"/>
            </svg>
            <span class="spinner" id="btnSpinner"></span>
          </button>

        </form>

      <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="card-footer">
      <?= date('Y') ?> &copy; TIC Dtel Telecom &mdash; Todos os direitos reservados
    </div>

  </div>
</div>

<script>
// ── Toggle visibilidade da senha ─────────────
const senhaInput = document.getElementById('senha');
const eyeBtn     = document.getElementById('toggleSenha');
const eyeIcon    = document.getElementById('eyeIcon');

eyeBtn.addEventListener('click', () => {
    const visible = senhaInput.type === 'text';
    senhaInput.type = visible ? 'password' : 'text';
    eyeIcon.innerHTML = visible
        ? '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>'
        : '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
});

// ── Spinner no submit ────────────────────────
const form       = document.getElementById('loginForm');
const btnSubmit  = document.getElementById('btnSubmit');
const btnText    = document.getElementById('btnText');
const btnArrow   = document.getElementById('btnArrow');
const btnSpinner = document.getElementById('btnSpinner');

if (form) {
    form.addEventListener('submit', (e) => {
        // Validação básica client-side
        const email = document.getElementById('email').value.trim();
        const senha = document.getElementById('senha').value.trim();
        if (!email || !senha) { e.preventDefault(); return; }

        btnSubmit.disabled   = true;
        btnText.textContent  = 'Verificando…';
        btnArrow.style.display  = 'none';
        btnSpinner.style.display = 'block';
    });
}
</script>
</body>
</html>