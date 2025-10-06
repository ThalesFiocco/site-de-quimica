<?php
// ventilador_teste.php
session_start();

/** Classe modelo (OO) */
class Ventilador {
    public $marca;
    public $modelo;
    public $velocidade; // 0..3 (0 = parado)
    private $ligado = false;
    private $oscilando = false;

    public function __construct($marca = "", $modelo = "", $ligado = false, $velocidade = 0, $oscilando = false) {
        $this->marca      = $marca;
        $this->modelo     = $modelo;
        $this->ligado     = (bool)$ligado;
        $this->velocidade = is_numeric($velocidade) ? max(0, min(3, (int)$velocidade)) : 0;
        $this->oscilando  = (bool)$oscilando;
    }

    public function ligar(): string {
        if ($this->ligado) return "O ventilador já está ligado!";
        $this->ligado = true;
        if ($this->velocidade <= 0) $this->velocidade = 1;
        return "O ventilador foi ligado na velocidade {$this->velocidade}.";
    }

    public function desligar(): string {
        if (!$this->ligado) return "O ventilador já está desligado!";
        $this->ligado = false;
        return "O ventilador foi desligado.";
    }

    public function setVelocidade($v): string {
        $v = is_numeric($v) ? (int)$v : 0;
        $this->velocidade = max(0, min(3, $v));
        return "Velocidade ajustada para {$this->velocidade}.";
    }

    public function aumentarVelocidade(): string {
        if ($this->velocidade < 3) {
            $this->velocidade++;
        }
        return "Velocidade atual: {$this->velocidade}.";
    }

    public function diminuirVelocidade(): string {
        if ($this->velocidade > 0) {
            $this->velocidade--;
        }
        return "Velocidade atual: {$this->velocidade}.";
    }

    public function toggleOscilacao(?bool $force = null): string {
        $this->oscilando = is_bool($force) ? $force : !$this->oscilando;
        return $this->oscilando ? "Oscilação ativada." : "Oscilação desativada.";
    }

    public function status(): string {
        if ($this->ligado) {
            $rotulo = self::rotuloVelocidade($this->velocidade);
            $osc = $this->oscilando ? 'com oscilação' : 'sem oscilação';
            return "Status: Ligado na velocidade {$this->velocidade} ({$rotulo}), {$osc}.";
        }
        return "Status: Desligado.";
    }

    public function isLigado(): bool { return $this->ligado; }
    public function isOscilando(): bool { return $this->oscilando; }

    public static function rotuloVelocidade(int $v): string {
        switch ($v) {
            case 1: return 'Baixa';
            case 2: return 'Média';
            case 3: return 'Alta';
            default: return 'Parado';
        }
    }
}

/** Estado inicial na sessão */
if (!isset($_SESSION['ventilador'])) {
    $_SESSION['ventilador'] = [
        'marca'      => 'Arno',
        'modelo'     => 'Turbo Silence',
        'ligado'     => false,
        'velocidade' => 1,
        'oscilando'  => false,
    ];
}

/** Reconstrói o objeto a partir da sessão */
$fanData = $_SESSION['ventilador'];
$fan = new Ventilador(
    $fanData['marca'],
    $fanData['modelo'],
    $fanData['ligado'],
    $fanData['velocidade'],
    $fanData['oscilando']
);

$mensagem = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'salvar') {
        $fan->marca      = trim($_POST['marca'] ?? '');
        $fan->modelo     = trim($_POST['modelo'] ?? '');
        if (isset($_POST['velocidade'])) {
            $fan->setVelocidade($_POST['velocidade']);
        }
        // Checkbox ausente deve significar "false"
        $fan->toggleOscilacao(isset($_POST['oscilando']));
        $mensagem = "Dados do ventilador atualizados.";
    } elseif ($acao === 'ligar') {
        $mensagem = $fan->ligar();
    } elseif ($acao === 'desligar') {
        $mensagem = $fan->desligar();
    } elseif ($acao === 'vel_up') {
        $mensagem = $fan->aumentarVelocidade();
    } elseif ($acao === 'vel_down') {
        $mensagem = $fan->diminuirVelocidade();
    } elseif ($acao === 'toggle_osc') {
        $mensagem = $fan->toggleOscilacao();
    } elseif ($acao === 'status') {
        $mensagem = $fan->status();
    }

    // Persiste o estado na sessão
    $_SESSION['ventilador'] = [
        'marca'      => $fan->marca,
        'modelo'     => $fan->modelo,
        'ligado'     => $fan->isLigado(),
        'velocidade' => $fan->velocidade,
        'oscilando'  => $fan->isOscilando(),
    ];

    // Evita reenvio do form ao atualizar
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Lê novamente para refletir o último estado
$fanData = $_SESSION['ventilador'];
$fan = new Ventilador(
    $fanData['marca'],
    $fanData['modelo'],
    $fanData['ligado'],
    $fanData['velocidade'],
    $fanData['oscilando']
);

$velRotulo = Ventilador::rotuloVelocidade((int)$fan->velocidade);
$on = $fan->isLigado();
$osc = $fan->isOscilando();

// define durações de animação em função da velocidade
$spin = '1.6s'; // v1
if ((int)$fan->velocidade === 2) $spin = '1.0s';
if ((int)$fan->velocidade === 3) $spin = '0.6s';
$swing = '2.4s';
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Teste OO em PHP — Ventilador</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
    :root {
        --bg: #f6f7fb;
        --card: #ffffff;
        --text: #1f2937;
        --muted: #6b7280;
        --primary: #2563eb;
        --ring: rgba(37,99,235,.2);
        --ok: #10b981;
        --off: #9ca3af;
        --warn: #f59e0b;
        --shadow: 0 10px 30px rgba(2,6,23,.10);
        --radius: 16px;
    }
    * { box-sizing: border-box; }
    body {
        margin: 0; font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, "Helvetica Neue", Arial;
        background: linear-gradient(180deg, #eef2ff 0%, #f6f7fb 60%);
        color: var(--text);
        display: grid; place-items: center; min-height: 100dvh; padding: 24px;
    }
    .wrap { width: 100%; max-width: 980px; display: grid; gap: 24px; }
    .grid { display: grid; grid-template-columns: 1.1fr .9fr; gap: 24px; }
    @media (max-width: 860px){ .grid { grid-template-columns: 1fr; } }
    .card {
        background: var(--card); border-radius: var(--radius); box-shadow: var(--shadow); padding: 22px;
        border: 1px solid #eef2ff;
    }
    h1 { margin: 0 0 6px; font-size: clamp(22px, 2.8vw, 28px); }
    p.lead { margin: 0; color: var(--muted); }
    .statusBox { display: grid; grid-template-columns: 120px 1fr; gap: 18px; align-items: center; }
    .lamp {
        width: 90px; aspect-ratio: 1; border-radius: 50%;
        background: var(--off); box-shadow: inset 0 -10px 18px rgba(0,0,0,.12);
        position: relative; transition: .25s ease;
    }
    .lamp.on  { background: var(--ok); box-shadow: 0 0 0 6px var(--ring), inset 0 -10px 18px rgba(0,0,0,.12); }
    .lamp::after {
        content: ""; position: absolute; inset: 0; border-radius: 50%;
        background: radial-gradient(closest-side, rgba(255,255,255,.65), rgba(255,255,255,0) 70%);
        mix-blend-mode: screen; pointer-events: none;
    }
    .box { display: grid; gap: 6px; align-items: center; }
    .badge { display: inline-block; padding: 6px 10px; border-radius: 999px; font-size: 12px; background: #eef2ff; color: #374151; }
    .row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-top: 10px; }
    .row input, .row select {
        width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 10px; background: #fff;
        outline: none; transition: border .15s ease, box-shadow .15s ease;
    }
    .row input:focus, .row select:focus { border-color: var(--primary); box-shadow: 0 0 0 4px var(--ring); }
    .btns { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px; }
    button {
        appearance: none; border: 0; padding: 10px 14px; border-radius: 10px;
        background: var(--primary); color: white; cursor: pointer; font-weight: 600;
        box-shadow: var(--shadow); transition: transform .06s ease, filter .2s;
    }
    button.secondary { background: #111827; }
    button.warn { background: var(--warn); color: #111; }
    button:active { transform: translateY(1px); }
    .msg { margin-top: 8px; color: #111827; font-weight: 600; min-height: 20px; }
    .mini {
        display: grid; place-items: center; background: #0b1220; border-radius: var(--radius); padding: 16px; color: #fff;
    }
    svg { width: 100%; height: auto; max-width: 360px; }
    .hint { color: var(--muted); font-size: 13px; margin-top: 8px; }
    .footer { text-align: center; color: var(--muted); font-size: 13px; }

    /* animações */
    @keyframes spin { to { transform: rotate(360deg); } }
    @keyframes swing { 0% { transform: rotate(-12deg); } 100% { transform: rotate(12deg); } }

    .head.osc { animation: swing var(--swing) ease-in-out infinite alternate; transform-origin: 180px 110px; }
    .blades.on { animation: spin var(--spin) linear infinite; transform-origin: 180px 110px; }
</style>
</head>
<body>
<div class="wrap">

    <div class="card">
        <h1>Teste OO em PHP — Classe <code>Ventilador</code></h1>
        <p class="lead">Ligue/desligue, ajuste a <strong>velocidade</strong> e a <strong>oscilação</strong>. Veja a luz e o gráfico animado.</p>
    </div>

    <div class="grid">
        <!-- Painel de Estado -->
        <div class="card">
            <div class="statusBox">
                <div class="lamp <?php echo $on ? 'on' : ''; ?>"></div>
                <div class="box">
                    <div>
                        <span class="badge"><?php echo htmlspecialchars($on ? 'Ligado' : 'Desligado'); ?></span>
                        <span class="badge"><?php echo htmlspecialchars('Vel. ' . (int)$fan->velocidade . ' (' . $velRotulo . ')'); ?></span>
                        <span class="badge"><?php echo htmlspecialchars($osc ? 'Oscilando' : 'Fixo'); ?></span>
                    </div>
                    <div><strong>Marca:</strong> <?php echo htmlspecialchars($fan->marca ?: '—'); ?></div>
                    <div><strong>Modelo:</strong> <?php echo htmlspecialchars($fan->modelo ?: '—'); ?></div>
                    <div class="msg"><?php echo htmlspecialchars($fan->status()); ?></div>
                </div>
            </div>

            <div class="btns">
                <form method="post" style="display:inline"><input type="hidden" name="acao" value="ligar"><button type="submit">Ligar</button></form>
                <form method="post" style="display:inline"><input type="hidden" name="acao" value="desligar"><button class="secondary" type="submit">Desligar</button></form>
                <form method="post" style="display:inline"><input type="hidden" name="acao" value="vel_down"><button class="warn" type="submit">Vel −</button></form>
                <form method="post" style="display:inline"><input type="hidden" name="acao" value="vel_up"><button class="warn" type="submit">Vel +</button></form>
                <form method="post" style="display:inline"><input type="hidden" name="acao" value="toggle_osc"><button type="submit"><?php echo $osc ? 'Parar Oscilação' : 'Oscilar'; ?></button></form>
                <form method="post" style="display:inline"><input type="hidden" name="acao" value="status"><button class="secondary" type="submit">Ver Status</button></form>
            </div>

            <p class="hint">Dica: a luz verde indica <strong>ligado</strong>. Use os botões para ajustar.</p>
        </div>

        <!-- Formulário de Atributos + Gráfico simples -->
        <div class="card">
            <form method="post">
                <div class="row">
                    <div>
                        <label>Marca</label>
                        <input name="marca" value="<?php echo htmlspecialchars($fan->marca); ?>" placeholder="Ex.: Arno">
                    </div>
                    <div>
                        <label>Modelo</label>
                        <input name="modelo" value="<?php echo htmlspecialchars($fan->modelo); ?>" placeholder="Ex.: Turbo">
                    </div>
                    <div>
                        <label>Velocidade</label>
                        <select name="velocidade">
                            <?php for ($v=0; $v<=3; $v++): ?>
                                <option value="<?php echo $v; ?>" <?php echo ((int)$fan->velocidade === $v) ? 'selected' : ''; ?>><?php echo $v . ' - ' . htmlspecialchars(Ventilador::rotuloVelocidade($v)); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="row" style="grid-template-columns: 1fr;">
                    <label style="display:flex; gap:8px; align-items:center;">
                        <input type="checkbox" name="oscilando" value="1" <?php echo $osc ? 'checked' : ''; ?>> Oscilando
                    </label>
                </div>
                <div class="btns">
                    <input type="hidden" name="acao" value="salvar">
                    <button type="submit">Salvar atributos</button>
                </div>
            </form>

            <div class="mini" aria-label="Representação gráfica do ventilador" style="--spin: <?php echo $spin; ?>; --swing: <?php echo $swing; ?>;">
                <!-- SVG do ventilador; gira conforme velocidade e oscila quando ativado -->
                <svg viewBox="0 0 360 220" role="img" aria-labelledby="fanTitle fanDesc">
                    <title id="fanTitle">Ventilador animado</title>
                    <desc id="fanDesc">As pás giram quando ligado, e a cabeça oscila quando oscilação está ativa.</desc>
                    <!-- chão -->
                    <rect x="0" y="200" width="360" height="4" fill="#334155" opacity=".6"/>

                    <!-- base -->
                    <g fill="#64748b">
                        <ellipse cx="180" cy="198" rx="60" ry="10" opacity=".8"/>
                        <rect x="170" y="150" width="20" height="46" rx="8"/>
                    </g>

                    <!-- cabeça (oscilação) -->
                    <g class="head <?php echo $osc ? 'osc' : ''; ?>" transform="translate(0,0)">
                        <!-- suporte -->
                        <rect x="175" y="130" width="10" height="20" rx="4" fill="#94a3b8"/>
                        <!-- carenagem -->
                        <circle cx="180" cy="110" r="40" fill="#93c5fd"/>
                        <circle cx="180" cy="110" r="36" fill="#60a5fa"/>
                        <!-- grade -->
                        <circle cx="180" cy="110" r="34" fill="none" stroke="#e5e7eb" stroke-width="2"/>
                        <circle cx="180" cy="110" r="20" fill="none" stroke="#e5e7eb" stroke-width="1.5" opacity=".7"/>
                        <g stroke="#e5e7eb" opacity=".6">
                            <line x1="180" y1="76" x2="180" y2="144" />
                            <line x1="146" y1="110" x2="214" y2="110" />
                            <line x1="156" y1="86" x2="204" y2="134" />
                            <line x1="156" y1="134" x2="204" y2="86" />
                        </g>

                        <!-- pás -->
                        <g class="blades <?php echo $on && (int)$fan->velocidade > 0 ? 'on' : ''; ?>">
                            <circle cx="180" cy="110" r="6" fill="#0b1220"/>
                            <path d="M180 110 C 190 90, 202 90, 210 110 C 198 112, 190 114, 180 110" fill="#0ea5e9"/>
                            <path d="M180 110 C 160 100, 150 112, 160 130 C 170 122, 175 116, 180 110" fill="#0ea5e9"/>
                            <path d="M180 110 C 176 128, 186 142, 206 140 C 196 130, 188 120, 180 110" fill="#0ea5e9"/>
                        </g>

                        <!-- vento quando ligado -->
                        <?php if ($on && (int)$fan->velocidade > 0): ?>
                        <g stroke="#93c5fd" stroke-width="2" fill="none" opacity=".7">
                            <path d="M230 100 q 20 -10 40 0" />
                            <path d="M230 116 q 24 8 44 0" />
                        </g>
                        <?php endif; ?>
                    </g>
                </svg>
                <div class="hint">As pás giram quando o ventilador está <strong>ligado</strong> e "Oscilar" faz a cabeça balançar.</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <strong>Atividade:</strong> peça aos alunos para <em>criar um método</em> <code>timerDesligar($segundos)</code> que desligue após X segundos
        e mostre a mensagem correspondente no painel de status.
    </div>
</div>
</body>
</html>
