/* --- Funções de Utilidade --- */
function openModal(modalId) {
  document.getElementById(modalId).style.display = "block";
  document.body.style.overflow = "hidden"; // Previne scroll da página
}

function closeModal(modalId) {
  document.getElementById(modalId).style.display = "none";
  document.body.style.overflow = "auto"; // Restaura scroll da página
}

// Fechar modal ao clicar fora dele
window.onclick = function (event) {
  const modals = document.querySelectorAll(".modal");
  modals.forEach((modal) => {
    if (event.target === modal) {
      modal.style.display = "none";
      document.body.style.overflow = "auto";
    }
  });
};

/**
 * Exibe uma notificação toast na tela.
 * @param {string} message A mensagem a ser exibida.
 * @param {string} type O tipo de toast ('success', 'error', 'info').
 */
function showToast(message, type = 'success') {
  const toastContainer = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.textContent = message;
  toastContainer.appendChild(toast);

  setTimeout(() => {
    toast.classList.add('show');
  }, 100);

  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => {
      toast.remove();
    }, 500);
  }, 3000);
}

/**
 * Função genérica para popular um elemento <select> a partir de um endpoint da API.
 * @param {string} selectId - O ID do elemento <select>.
 * @param {string} endpoint - A URL do endpoint da API.
 * @param {function} optionTextFormatter - Uma função que formata o texto de cada <option>.
 * @param {string} placeholder - O texto para a primeira opção desabilitada.
 * @returns {Promise<void>}
 */
async function populateSelect(selectId, endpoint, optionTextFormatter, placeholder) {
  try {
    const response = await fetch(endpoint);
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
    const data = await response.json();

    const select = document.getElementById(selectId);
    if (!select) return; // Se o select não existir na página, apenas saia da função.

    select.innerHTML = `<option value="">${placeholder}</option>`;

    data.forEach(item => {
      const option = document.createElement("option");
      option.value = item.id;
      option.textContent = optionTextFormatter(item);
      select.appendChild(option);
    });
  } catch (error) {
    console.error(`Erro ao carregar dados para ${selectId}:`, error);
    showToast(`Falha ao carregar ${selectId.replace('_', ' ')}.`, 'error');
  }
}

/* --- Funções de Inicialização --- */
document.addEventListener("DOMContentLoaded", function () {
  loadDynamicSelects();
  setupModalForms();
  setupEditOSForm();
  gerarNumeroOS();
  setupLogoutButton();
  loadUserInfo();
  setupOSForm();
});

/**
 * Encontra e popula todos os selects marcados com a classe 'dynamic-select'.
 */
function loadDynamicSelects() {
  const formatters = {
    'default': item => item.nome,
    'equipamento': item => `${item.codigo} - ${item.nome}`,
    'localizacao': item => `${item.setor} - ${item.nome}`,
  };

  const selects = document.querySelectorAll('.dynamic-select');
  selects.forEach(select => {
    const selectId = select.id;
    const endpoint = select.dataset.endpoint;
    const formatterKey = select.dataset.formatter || 'default';
    const placeholder = select.dataset.placeholder || 'Selecione uma opção';
    const formatter = formatters[formatterKey];

    populateSelect(selectId, endpoint, formatter, placeholder);
  });
}

/**
 * Busca as informações do usuário da sessão e exibe na tela.
 */
async function loadUserInfo() {
  const userGreetingEl = document.getElementById('user-greeting');
  if (!userGreetingEl) return;

  try {
    // A API get_user_info.php retornará os dados da sessão.
    const response = await fetch('api/get_user_info.php');
    const result = await response.json();

    if (result.success && result.data.user_name) {
      userGreetingEl.innerHTML = `<i class="fas fa-user"></i> Olá, <strong>${result.data.user_name}</strong>`;
    } else {
      userGreetingEl.textContent = 'Usuário não identificado.';
    }
  } catch (error) {
    console.error('Erro ao carregar informações do usuário:', error);
  }
}

/**
 * Configura o botão de logout.
 */
function setupLogoutButton() {
  const logoutBtn = document.getElementById('logout-btn');
  if (!logoutBtn) return;

  logoutBtn.addEventListener('click', async () => {
    try {
      const response = await fetch('api/logout_user.php');
      const result = await response.json();

      if (result.success) {
        showToast('Você foi desconectado. Redirecionando...', 'info');
        setTimeout(() => {
          window.location.href = 'login.html';
        }, 1500);
      }
    } catch (error) {
      console.error('Erro ao fazer logout:', error);
    }
  });
}

/* --- Funções da Ordem de Serviço --- */

function gerarNumeroOS() {
  const numOsInput = document.getElementById("num_os");
  if (!numOsInput) return; // Se o campo não existe, não faz nada.

  fetch("api/get_next_os_number.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        numOsInput.value = data.data.numero_os;
      } else {
        console.error("Erro ao gerar número da OS:", data.message);
      }
    })
    .catch((error) => {
      console.error("Erro ao gerar número da OS:", error);
    });
}

function setupOSForm() {
  const osForm = document.querySelector(".os-form");
  if (!osForm) return; // Se o formulário não existe, não faz nada.
  osForm.addEventListener("submit", function (e) {
    e.preventDefault();

    // Validar se todos os campos obrigatórios estão preenchidos
    const requiredFields = [
      "num_os",
      "equipamento",
      "localizacao",
      "tipo_manutencao",
      "area_manutencao",
      "prioridade",
      "solicitante",
      "data_inicial",
      "descricao_problema",
    ];

    let isValid = true;
    requiredFields.forEach((fieldId) => {
      const field = document.getElementById(fieldId);
      if (!field.value.trim()) {
        field.style.borderColor = "#dc3545";
        isValid = false;
      } else {
        field.style.borderColor = "";
      }
    });

    if (!isValid) {
      showToast("Por favor, preencha todos os campos obrigatórios.", "error");
      return;
    }

    // Preparar dados do formulário
    const formData = new FormData(this);

    // Mostrar loading
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
    submitBtn.disabled = true;

    // Enviar dados
    fetch("api/save_ordem_servico.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showToast(`OS ${data.data.numero_os} cadastrada com sucesso!`);
          
          // Limpar formulário
          this.reset();

          // Gerar novo número da OS
          gerarNumeroOS();

          // Limpar bordas vermelhas
          requiredFields.forEach((fieldId) => {
            document.getElementById(fieldId).style.borderColor = "";
          });
        } else {
          showToast("Erro: " + data.message, "error");
        }
      })
      .catch((error) => {
        console.error("Erro:", error);
        showToast("Erro ao conectar com a API.", "error");
      })
      .finally(() => {
        // Restaurar botão
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      });
  });
}

/* --- Funções dos Modais de Cadastro --- */

/**
 * Função genérica para configurar a submissão de formulários de modais.
 * @param {string} formId - O ID do formulário.
 * @param {string} endpoint - A URL do endpoint da API para salvar.
 * @param {string} modalId - O ID do modal a ser fechado.
 * @param {function} successCallback - Função a ser executada em caso de sucesso.
 */
function handleModalFormSubmit(formId, endpoint, modalId, successCallback) {
  const form = document.getElementById(formId);
  if (!form) return; // Se o formulário não existir na página, saia da função.
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch(endpoint, { method: 'POST', body: formData })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast(data.message || 'Operação realizada com sucesso!');
          closeModal(modalId);
          this.reset();
          if (successCallback) successCallback();
        } else {
          showToast(data.message || 'Ocorreu um erro.', 'error');
        }
      })
      .catch(error => {
        console.error('Erro no formulário do modal:', error);
        showToast('Erro de conexão ao salvar.', 'error');
      });
  });
}

function setupModalForms() {
  handleModalFormSubmit('form-equipamento', 'api/save_equipamento.php', 'equipamento-modal', () =>
    populateSelect('equipamento', 'api/get_equipamentos.php', item => `${item.codigo} - ${item.nome}`, 'Selecione um equipamento')
  );
  handleModalFormSubmit('form-localizacao', 'api/save_localizacao.php', 'localizacao-modal', () =>
    populateSelect('localizacao', 'api/get_localizacoes.php', item => `${item.setor} - ${item.nome}`, 'Selecione uma localização')
  );
  handleModalFormSubmit('form-tipo-manutencao', 'api/save_tipo_manutencao.php', 'tipo-manutencao-modal', () =>
    populateSelect('tipo_manutencao', 'api/get_tipos_manutencao.php', item => item.nome, 'Selecione o tipo')
  );
  handleModalFormSubmit('form-area-manutencao', 'api/save_area_manutencao.php', 'area-manutencao-modal', () =>
    populateSelect('area_manutencao', 'api/get_areas_manutencao.php', item => item.nome, 'Selecione a área')
  );
}

/* --- Funções de Edição e Impressão --- */

/**
 * Abre o modal de edição, busca e preenche os dados da OS.
 * @param {number} osId O ID da Ordem de Serviço a ser editada.
 */
async function openEditModal(osId) {
  try {
    // Busca os dados da OS
    const response = await fetch(`api/get_os_by_id.php?id=${osId}`);
    if (!response.ok) throw new Error('Falha ao buscar dados da OS.');
    
    const result = await response.json();
    if (!result.success) {
      showToast(result.message, 'error');
      return;
    }

    const os = result.data;
    const form = document.getElementById('form-editar-os');

    // Preenche os campos do formulário
    form.querySelector('#edit_num_os').value = os.numero_os;
    form.querySelector('#edit_solicitante').value = os.solicitante;
    form.querySelector('#edit_data_inicial').value = os.data_inicial;
    form.querySelector('#edit_data_final').value = os.data_final || '';
    form.querySelector('#edit_descricao_problema').value = os.descricao_problema;
    
    // Seleciona os valores nos selects
    form.querySelector('#edit_equipamento').value = os.equipamento_id;
    form.querySelector('#edit_localizacao').value = os.localizacao_id;
    form.querySelector('#edit_tipo_manutencao').value = os.tipo_manutencao_id;
    form.querySelector('#edit_area_manutencao').value = os.area_manutencao_id;
    form.querySelector('#edit_prioridade').value = os.prioridade;
    form.querySelector('#edit_status').value = os.status;

    // Armazena o ID da OS no formulário para ser enviado na atualização
    let osIdInput = form.querySelector('input[name="os_id"]');
    if (!osIdInput) {
      osIdInput = document.createElement('input');
      osIdInput.type = 'hidden';
      osIdInput.name = 'os_id';
      form.appendChild(osIdInput);
    }
    osIdInput.value = os.id;

    openModal('editar-os-modal');

  } catch (error) {
    console.error('Erro ao abrir modal de edição:', error);
    showToast('Não foi possível carregar os dados para edição.', 'error');
  }
}

/**
 * Configura o formulário de edição de OS.
 */
function setupEditOSForm() {
  const form = document.getElementById('form-editar-os');
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Atualizando...';
    submitBtn.disabled = true;

    fetch('api/update_ordem_servico.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showToast('OS atualizada com sucesso!');
        closeModal('editar-os-modal');
        // Recarrega a lista se a função global existir (na página gerenciar.html)
        if (window.reloadOrdensServico) window.reloadOrdensServico();
      } else {
        showToast(data.message, 'error');
      }
    })
    .catch(error => {
      console.error('Erro ao atualizar OS:', error);
      showToast('Erro de conexão ao atualizar a OS.', 'error');
    })
    .finally(() => {
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    });
  });
}

/**
 * Gera uma janela de impressão para uma OS específica.
 * @param {number} osId O ID da Ordem de Serviço a ser impressa.
 */
async function printOS(osId) {
  try {
    const response = await fetch(`api/get_os_by_id.php?id=${osId}`);
    if (!response.ok) throw new Error('Falha ao buscar dados da OS para impressão.');

    const result = await response.json();
    if (!result.success) {
      showToast(result.message, 'error');
      return;
    }

    const os = result.data;
    const printWindow = window.open('', '_blank', 'height=600,width=800');
    printWindow.document.write(`
      <html>
        <head>
          <title>Impressão OS ${os.numero_os}</title>
          <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; color: #333; }
            .print-container { max-width: 800px; margin: auto; }
            .print-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
            .print-header h1 { margin: 0; font-size: 24px; }
            .print-header .os-number { font-size: 18px; font-weight: bold; }
            .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px 20px; margin-bottom: 20px; }
            .detail-item { border-bottom: 1px solid #eee; padding-bottom: 8px; }
            .detail-item strong { display: block; font-size: 12px; color: #555; margin-bottom: 4px; text-transform: uppercase; }
            .detail-item span { font-size: 16px; }
            .section { margin-top: 30px; }
            .section h2 { border-bottom: 1px solid #ccc; padding-bottom: 5px; font-size: 18px; }
            .section p, .section pre { font-size: 16px; white-space: pre-wrap; word-wrap: break-word; background: #f9f9f9; padding: 15px; border-radius: 4px; }
            .print-footer { margin-top: 50px; padding-top: 20px; border-top: 1px solid #ccc; display: flex; justify-content: space-around; text-align: center; }
            .signature-line { border-top: 1px solid #000; width: 250px; margin: 40px auto 0 auto; padding-top: 5px; }
            @media print {
              body { padding: 0; }
              .print-container { box-shadow: none; border: none; }
            }
          </style>
        </head>
        <body>
          <div class="print-container">
            <div class="print-header">
              <h1>Ordem de Serviço</h1>
              <span class="os-number">Nº: ${os.numero_os}</span>
            </div>

            <div class="section">
              <h2>Detalhes da Solicitação</h2>
              <div class="details-grid">
                <div class="detail-item">
                  <strong>Equipamento</strong>
                  <span>${os.equipamento_codigo} - ${os.equipamento_nome}</span>
                </div>
                <div class="detail-item">
                  <strong>Localização</strong>
                  <span>${os.localizacao_setor} - ${os.localizacao_nome}</span>
                </div>
                <div class="detail-item">
                  <strong>Solicitante</strong>
                  <span>${os.solicitante}</span>
                </div>
                <div class="detail-item">
                  <strong>Data de Abertura</strong>
                  <span>${formatarData(os.data_inicial)}</span>
                </div>
                <div class="detail-item">
                  <strong>Prioridade</strong>
                  <span style="text-transform: capitalize;">${os.prioridade}</span>
                </div>
                <div class="detail-item">
                  <strong>Status Atual</strong>
                  <span style="text-transform: capitalize;">${os.status.replace('_', ' ')}</span>
                </div>
              </div>
            </div>

            <div class="section">
              <h2>Descrição do Problema / Serviço Solicitado</h2>
              <pre>${os.descricao_problema}</pre>
            </div>

            <div class="section">
              <h2>Detalhes da Manutenção</h2>
              <div class="details-grid">
                <div class="detail-item">
                  <strong>Tipo de Manutenção</strong>
                  <span>${os.tipo_manutencao_nome}</span>
                </div>
                <div class="detail-item">
                  <strong>Área Responsável</strong>
                  <span>${os.area_manutencao_nome} (Resp: ${os.area_responsavel})</span>
                </div>
                <div class="detail-item">
                  <strong>Data de Conclusão</strong>
                  <span>${os.data_final ? formatarData(os.data_final) : 'Não concluída'}</span>
                </div>
              </div>
            </div>

            <div class="print-footer">
              <div>
                <div class="signature-line">Assinatura do Solicitante</div>
              </div>
              <div>
                <div class="signature-line">Assinatura do Responsável</div>
              </div>
            </div>

          </div>
          <script>
            window.onload = function() {
              // Espera um pouco para garantir que tudo foi renderizado
              setTimeout(function() {
              window.print();
              window.onafterprint = function() { window.close(); };
              }, 200);
            };
          </script>
        </body>
      </html>
    `);
    printWindow.document.close();
  } catch (error) {
    console.error('Erro ao imprimir OS:', error);
    showToast('Não foi possível gerar a impressão.', 'error');
  }
}

/* --- Funções de Formatação --- */
function formatarData(dataString) {
  if (!dataString) return "";
  const data = new Date(dataString);
  return data.toLocaleDateString("pt-BR");
}