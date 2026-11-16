<?php
// Recursos de apresentação: escape de texto, caminhos das capas e ícones.
if (!function_exists('h')) {
    function h($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}
function caminhoCapa($arquivo, $prefixo = '../public/imagens/') {
    if (is_string($arquivo) && basename($arquivo) === $arquivo
        && preg_match('/\.(jpg|jpeg|png|webp)$/i', $arquivo)
        && is_file(__DIR__ . '/../../public/imagens/' . $arquivo)) {
        return $prefixo . rawurlencode($arquivo);
    }
    return $prefixo . 'sem-capa.svg';
}
function icone($nome) {
    $caminhos = [
        'gamepad' => '<path d="M6 8h12a4 4 0 0 1 4 4v5a2 2 0 0 1-3.4 1.4L16 16H8l-2.6 2.4A2 2 0 0 1 2 17v-5a4 4 0 0 1 4-4Z"/><path d="M7 10v5m-2.5-2.5h5"/><circle cx="16" cy="11.5" r=".5"/><circle cx="18.5" cy="14" r=".5"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'list' => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.1M3 12h.1M3 18h.1"/>',
        'edit' => '<path d="m16 3 5 5-12 12H4v-5L16 3Z"/><path d="m13 6 5 5"/>',
        'trash' => '<path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7M14 10v7"/>',
        'arrow' => '<path d="M5 12h14m-6-6 6 6-6 6"/>',
        'upload' => '<path d="M12 16V3m-5 5 5-5 5 5M4 15v6h16v-6"/>',
    ];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($caminhos[$nome] ?? $caminhos['gamepad']) . '</svg>';
}
