(function () {
    function digitos(valor) {
        return String(valor || '').replace(/\D/g, '');
    }
    document.querySelectorAll('form[data-validar="cliente"]').forEach(function (form) {
        form.addEventListener('submit', function (evento) {
            var erros = [];
            var nome = form.nome.value.trim();
            var email = form.email.value.trim();
            var telefone = digitos(form.telefone.value);
            var senha = form.senha ? form.senha.value : '';
            var confirmar = form.confirmar_senha ? form.confirmar_senha.value : '';
            var senhaObrigatoria = form.getAttribute('data-senha-obrigatoria') === '1';
            if (nome.length < 3 || nome.length > 100) {
                erros.push('Informe um nome com pelo menos 3 caracteres.');
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                erros.push('Informe um e-mail válido.');
            }
            if (telefone.length < 10 || telefone.length > 11) {
                erros.push('Informe um telefone com DDD, usando 10 ou 11 dígitos.');
            }
            if (senhaObrigatoria || senha !== '' || confirmar !== '') {
                if (senha.length < 6 || senha.length > 72) {
                    erros.push('A senha deve ter entre 6 e 72 caracteres.');
                }
                if (senha !== confirmar) {
                    erros.push('As senhas não coincidem.');
                }
            }
            var caixa = form.querySelector('.js-erros');
            if (!caixa) {
                caixa = document.createElement('p');
                caixa.className = 'erro js-erros';
                caixa.setAttribute('role', 'alert');
                form.insertBefore(caixa, form.firstChild);
            }
            if (erros.length) {
                evento.preventDefault();
                caixa.hidden = false;
                caixa.textContent = erros.join(' ');
            } else {
                caixa.hidden = true;
            }
        });
    });
})();
