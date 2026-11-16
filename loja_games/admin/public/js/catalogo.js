'use strict';
if ('BroadcastChannel' in window) {
    const purchaseChannel = new BroadcastChannel('gamestock-compras');
    purchaseChannel.addEventListener('message', event => {
        if (event.data === 'compra-realizada') window.location.reload();
    });
}
// Categoria, ordenação e modo de exibição alteram apenas elementos já renderizados.
const grid = document.getElementById('games-grid');
const list = document.getElementById('games-list');
const categoryFilter = document.getElementById('category-filter');
const sortGames = document.getElementById('sort-games');
const countBadge = document.querySelector('.count-badge');
const emptyCategory = document.getElementById('category-empty');
let view = 'grade';
function updateCatalog() {
    if (!grid || !list) return;
    const category = categoryFilter.value;
    const compare = (a, b) => {
        const order = sortGames.value;
        if (order === 'preco_asc') return Number(a.dataset.price) - Number(b.dataset.price);
        if (order === 'preco_desc') return Number(b.dataset.price) - Number(a.dataset.price);
        if (order === 'estoque_desc') return Number(b.dataset.stock) - Number(a.dataset.stock);
        return a.dataset.title.localeCompare(b.dataset.title, 'pt-BR');
    };
    let visible = 0;
    [grid, list.querySelector('tbody')].forEach((container, index) => {
        [...container.children].sort(compare).forEach(item => {
            item.hidden = category !== '' && item.dataset.category !== category;
            if (index === 0 && !item.hidden) visible++;
            container.appendChild(item);
        });
    });
    countBadge.textContent = visible;
    grid.hidden = view !== 'grade' || visible === 0;
    list.hidden = view !== 'lista' || visible === 0;
    emptyCategory.hidden = visible !== 0;
}
categoryFilter.addEventListener('change', updateCatalog);
sortGames.addEventListener('change', updateCatalog);
document.querySelectorAll('[data-view]').forEach(button => {
    button.addEventListener('click', () => {
        view = button.dataset.view;
        document.querySelectorAll('[data-view]').forEach(other => {
            const selected = other.dataset.view === view;
            other.classList.toggle('selected', selected);
            other.setAttribute('aria-pressed', String(selected));
        });
        updateCatalog();
    });
});
const deleteDialog = document.getElementById('delete-dialog');
const deleteMessage = document.getElementById('delete-message');
const confirmDelete = document.getElementById('confirm-delete');
if (deleteDialog && deleteMessage && confirmDelete) {
let pendingId = null;
document.querySelectorAll('.delete-button').forEach(button => {
    button.addEventListener('click', () => {
        pendingId = button.dataset.id;
        deleteMessage.textContent = button.dataset.titulo;
        confirmDelete.disabled = false;
        confirmDelete.textContent = 'Excluir jogo';
        deleteDialog.showModal();
    });
});
deleteDialog.addEventListener('close', () => { pendingId = null; });
confirmDelete.addEventListener('click', () => {
    if (!pendingId) return;
    confirmDelete.disabled = true;
    confirmDelete.textContent = 'Excluindo…';
    window.location.assign('../controllers/deletar_game.php?id=' + encodeURIComponent(pendingId) + '&token=' + encodeURIComponent(deleteDialog.dataset.token));
});

}
