'use strict';
const input = document.getElementById('capa');
const preview = document.getElementById('cover-preview');
const feedback = document.getElementById('cover-feedback');
let previewUrl;
input.addEventListener('change', () => {
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    const file = input.files[0];
    if (file) {
        previewUrl = URL.createObjectURL(file);
        preview.src = previewUrl;
        feedback.textContent = file.name;
    } else {
        preview.src = '../public/imagens/sem-capa.svg';
        feedback.textContent = 'A capa aparecerá no catálogo após o cadastro.';
    }
});
