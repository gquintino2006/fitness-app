document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.flash').forEach(function (el) {
        setTimeout(function () { el.style.display = 'none'; }, 4000);
    });

    const tabela = document.getElementById('linhasSeries');
    const btnAdd = document.getElementById('addLinha');

    if (tabela && btnAdd) {
        btnAdd.addEventListener('click', function () {
            const nova = tabela.querySelector('tr').cloneNode(true);
            nova.querySelectorAll('input').forEach(i => i.value = '');
            nova.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
            tabela.appendChild(nova);
        });

        tabela.addEventListener('click', function (ev) {
            if (ev.target.classList.contains('remover-linha')) {
                if (tabela.querySelectorAll('tr').length > 1) {
                    ev.target.closest('tr').remove();
                }
            }
        });
    }
});
