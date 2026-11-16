'use strict';
// Atualiza outra aba do catálogo; o estado Comprado sempre é lido do banco.
if ('BroadcastChannel' in window) {
    const channel = new BroadcastChannel('gamestock-compras');
    channel.postMessage('compra-realizada');
    channel.close();
}
