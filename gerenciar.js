document.addEventListener('DOMContentLoaded', () => {
    // Estado para armazenar os filtros e paginação atuais
    const state = {
        pagina: 1,
        limite: 10,
        busca: '',
        status: '',
        prioridade: '',
        data: ''
    };

    // Elementos do DOM
    const searchInput = document.getElementById('search-input');
    const statusFilter = document.getElementById('status-filter');
    const priorityFilter = document.getElementById('priority-filter');
    const dateFilter = document.getElementById('date-filter');
    const clearFiltersBtn = document.getElementById('clear-filters-btn');

    /**
     * Busca e renderiza as Ordens de Serviço.
     */
    async function loadOrdensServico() {
        const tableBody = document.getElementById('os-table-body');
        const loadingSpinner = document.getElementById('loading-spinner');
        const noResultsMessage = document.getElementById('no-results-message');

        tableBody.innerHTML = '';
        loadingSpinner.style.display = 'block';
        noResultsMessage.style.display = 'none';

        // Constrói a URL com os parâmetros de filtro
        const params = new URLSearchParams(state);
        const url = `api/get_ordens_servico.php?${params.toString()}`;

        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('Falha na requisição à API.');

            const result = await response.json();
            if (!result.success) throw new Error(result.message);

            if (result.data.ordens.length === 0) {
                noResultsMessage.style.display = 'block';
            } else {
                renderTable(result.data.ordens);
            }
            renderPagination(result.data.paginacao);

        } catch (error) {
            console.error('Erro ao carregar Ordens de Serviço:', error);
            showToast('Falha ao carregar dados. Tente novamente.', 'error');
            noResultsMessage.textContent = 'Erro ao carregar dados.';
            noResultsMessage.style.display = 'block';
        } finally {
            loadingSpinner.style.display = 'none';
        }
    }

    /**
     * Renderiza as linhas da tabela de OS.
     * @param {Array} ordens - A lista de ordens de serviço.
     */
    function renderTable(ordens) {
        const tableBody = document.getElementById('os-table-body');
        tableBody.innerHTML = '';

        ordens.forEach(os => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${os.numero_os}</td>
                <td>${os.equipamento_codigo} - ${os.equipamento_nome}</td>
                <td>${os.solicitante}</td>
                <td>${formatarData(os.data_inicial)}</td>
                <td><span class="badge priority-${os.prioridade.toLowerCase()}">${os.prioridade}</span></td>
                <td><span class="badge status-${os.status}">${os.status.replace('_', ' ')}</span></td>
                <td class="actions">
                    <button class="action-btn" onclick="openEditModal(${os.id})" title="Editar"><i class="fas fa-edit"></i></button>
                    <button class="action-btn" onclick="printOS(${os.id})" title="Imprimir"><i class="fas fa-print"></i></button>
                    <button class="action-btn delete-btn" onclick="deleteOS(${os.id}, '${os.numero_os}')" title="Excluir"><i class="fas fa-trash-alt"></i></button>
                </td>
            `;
            tableBody.appendChild(tr);
        });
    }

    /**
     * Renderiza os controles de paginação.
     * @param {object} paginacao - As informações de paginação da API.
     */
    function renderPagination(paginacao) {
        const { pagina_atual, total_paginas, total_registros } = paginacao;
        const controlsContainer = document.getElementById('pagination-controls');
        const infoContainer = document.getElementById('pagination-info');
        controlsContainer.innerHTML = '';

        infoContainer.textContent = `Página ${pagina_atual} de ${total_paginas} (${total_registros} registros)`;

        if (total_paginas <= 1) return;

        // Botão "Anterior"
        const prevBtn = document.createElement('button');
        prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
        prevBtn.disabled = pagina_atual === 1;
        prevBtn.onclick = () => changePage(pagina_atual - 1);
        controlsContainer.appendChild(prevBtn);

        // Lógica para exibir números de página (simplificada)
        for (let i = 1; i <= total_paginas; i++) {
            if (i === pagina_atual || Math.abs(i - pagina_atual) < 2 || i === 1 || i === total_paginas) {
                const pageBtn = document.createElement('button');
                pageBtn.textContent = i;
                pageBtn.className = i === pagina_atual ? 'active' : '';
                pageBtn.onclick = () => changePage(i);
                controlsContainer.appendChild(pageBtn);
            } else if (Math.abs(i - pagina_atual) === 2) {
                const dots = document.createElement('span');
                dots.textContent = '...';
                controlsContainer.appendChild(dots);
            }
        }

        // Botão "Próximo"
        const nextBtn = document.createElement('button');
        nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
        nextBtn.disabled = pagina_atual === total_paginas;
        nextBtn.onclick = () => changePage(pagina_atual + 1);
        controlsContainer.appendChild(nextBtn);
    }

    /**
     * Altera a página atual e recarrega os dados.
     * @param {number} newPage - O número da nova página.
     */
    function changePage(newPage) {
        state.pagina = newPage;
        loadOrdensServico();
    }

    /**
     * Aplica os filtros e recarrega os dados.
     */
    function applyFilters() {
        state.pagina = 1; // Sempre volta para a primeira página ao aplicar um filtro
        state.busca = searchInput.value;
        state.status = statusFilter.value;
        state.prioridade = priorityFilter.value;
        state.data = dateFilter.value;
        loadOrdensServico();
    }

    // Adiciona os event listeners para os filtros
    let debounceTimer;
    searchInput.addEventListener('keyup', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(applyFilters, 500); // Debounce para evitar requisições a cada tecla
    });

    statusFilter.addEventListener('change', applyFilters);
    priorityFilter.addEventListener('change', applyFilters);
    dateFilter.addEventListener('change', applyFilters);

    clearFiltersBtn.addEventListener('click', () => {
        searchInput.value = '';
        statusFilter.value = '';
        priorityFilter.value = '';
        dateFilter.value = '';
        applyFilters();
    });

    // Carrega os dados iniciais
    loadOrdensServico();

    // Expor a função de recarregamento para ser chamada após edição/exclusão
    window.reloadOrdensServico = loadOrdensServico;
});

/**
 * Exclui uma Ordem de Serviço após confirmação.
 * @param {number} osId - O ID da OS a ser excluída.
 * @param {string} osNumero - O número da OS para a mensagem de confirmação.
 */
async function deleteOS(osId, osNumero) {
    if (!confirm(`Tem certeza que deseja excluir a OS ${osNumero}? Esta ação não pode ser desfeita.`)) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('id', osId);

        const response = await fetch('api/delete_ordem_servico.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            window.reloadOrdensServico(); // Recarrega a tabela
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Erro ao excluir OS:', error);
        showToast('Falha na conexão ao tentar excluir a OS.', 'error');
    }
}

// Sobrescreve a função de sucesso do formulário de edição para recarregar a tabela
const originalSetupEditOSForm = window.setupEditOSForm;
window.setupEditOSForm = function() {
    originalSetupEditOSForm(); // Chama a função original
    const form = document.getElementById('form-editar-os');
    
    // Remove o listener antigo para evitar duplicação
    const newForm = form.cloneNode(true);
    form.parentNode.replaceChild(newForm, form);

    newForm.addEventListener('submit', function(e) {
        // A lógica de submit já está no script.js, só precisamos garantir que a tabela seja recarregada
        const originalThen = fetch.prototype.then;
        fetch.prototype.then = function(onFulfilled, onRejected) {
            const newOnFulfilled = (response) => {
                if (response.url.includes('update_ordem_servico.php')) {
                    response.clone().json().then(data => {
                        if (data.success) {
                            window.reloadOrdensServico();
                        }
                    });
                }
                return onFulfilled(response);
            };
            return originalThen.call(this, newOnFulfilled, onRejected);
        };
    });
};