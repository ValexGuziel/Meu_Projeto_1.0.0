document.addEventListener('DOMContentLoaded', () => {
    loadDashboardData();
    setupPdfExportButton();
});

async function loadDashboardData() {
    try {
        const response = await fetch('api/get_dashboard_stats.php');
        if (!response.ok) throw new Error('Falha ao carregar dados do dashboard.');

        const result = await response.json();
        if (!result.success) throw new Error(result.message);

        const data = result.data;

        // Atualiza os cards de resumo
        updateSummaryCards(data);

        // Renderiza os gráficos
        renderStatusChart(data.byStatus);
        renderPriorityChart(data.byPriority);
        renderMaintainerChart(data.byMaintainer);
        renderTopEquipmentChart(data.topEquipment);

    } catch (error) {
        console.error('Erro no dashboard:', error);
        // Opcional: exibir uma mensagem de erro na tela
    }
}

function setupPdfExportButton() {
    const exportBtn = document.getElementById('export-pdf-btn');
    if (!exportBtn) return;

    exportBtn.addEventListener('click', () => {
        // Desabilita o botão e mostra um estado de "carregando"
        exportBtn.disabled = true;
        exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gerando...';

        // Define o elemento que será exportado
        const dashboardContent = document.querySelector('.dashboard-container');
        
        // Usa html2canvas para capturar o conteúdo como uma imagem
        html2canvas(dashboardContent, {
            scale: 2, // Aumenta a resolução para melhor qualidade
            useCORS: true, // Permite carregar imagens de outras origens, se houver
            onclone: (document) => {
                // Remove o próprio botão de exportação da imagem capturada
                document.getElementById('export-pdf-btn').style.display = 'none';
            }
        }).then(canvas => {
            const { jsPDF } = window.jspdf;
            const imgData = canvas.toDataURL('image/png');

            const canvasWidth = canvas.width;
            const canvasHeight = canvas.height;
            const orientation = canvasWidth > canvasHeight ? 'l' : 'p'; // 'l' para paisagem, 'p' para retrato

            // Cria um PDF com as dimensões exatas da imagem capturada
            // A unidade 'px' com a conversão de 0.264583 (mm por pixel) ajuda a manter a escala
            const pdf = new jsPDF({
                orientation: orientation,
                unit: 'mm',
                format: [canvasWidth * 0.264583, canvasHeight * 0.264583]
            });

            // Adiciona a imagem ao PDF
            pdf.addImage(imgData, 'PNG', 0, 0, pdf.internal.pageSize.getWidth(), pdf.internal.pageSize.getHeight());
            pdf.save(`dashboard-relatorio-${new Date().toISOString().slice(0, 10)}.pdf`);

            // Reabilita o botão
            exportBtn.disabled = false;
            exportBtn.innerHTML = '<i class="fas fa-file-pdf"></i> Exportar para PDF';
        });
    });
}

function updateSummaryCards(data) {
    document.getElementById('total-os-value').textContent = data.totalOS || 0;
    document.getElementById('pending-os-value').textContent = data.byStatus?.pendente || 0;
    document.getElementById('in-progress-os-value').textContent = data.byStatus?.em_andamento || 0;

    const avgTimeValueEl = document.getElementById('avg-time-value');
    if (data.avgCompletionTime !== 'N/A') {
        avgTimeValueEl.innerHTML = `${data.avgCompletionTime} <span style="font-size: 0.5em; color: #6c757d;">dias</span>`;
    } else {
        avgTimeValueEl.innerHTML = `<span style="font-size: 0.8em;">N/A</span>`;
    }
}

function renderStatusChart(statusData) {
    const ctx = document.getElementById('statusChart').getContext('2d');

    const labels = Object.keys(statusData);
    const values = Object.values(statusData);

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels.map(l => l.replace('_', ' ')),
            datasets: [{
                label: 'OS por Status',
                data: values,
                backgroundColor: [
                    '#ffc107', // Pendente
                    '#17a2b8', // Em Andamento
                    '#28a745', // Concluída
                    '#6c757d'  // Cancelada
                ],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Ordens de Serviço por Status',
                    font: { size: 18 }
                }
            }
        }
    });
}

function renderTopEquipmentChart(equipmentData) {
    const ctx = document.getElementById('topEquipmentChart').getContext('2d');

    const labels = equipmentData.map(item => item.equipamento_nome);
    const values = equipmentData.map(item => item.total_os);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nº de Ordens de Serviço',
                data: values,
                backgroundColor: '#f57c00', // Laranja
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Top 5 Equipamentos com Mais OS',
                    font: { size: 18 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 } // Garante que o eixo Y só mostre números inteiros
                }
            }
        }
    });
}

function renderMaintainerChart(maintainerData) {
    const ctx = document.getElementById('maintainerChart').getContext('2d');

    const labels = maintainerData.map(item => item.responsavel);
    const values = maintainerData.map(item => item.total);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'OS por Manutentor',
                data: values,
                backgroundColor: [
                    '#36a2eb', '#ff6384', '#4bc0c0', '#ff9f40', '#9966ff', '#ffcd56', '#c9cbcf'
                ],
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y', // Gráfico de barras horizontais
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Ordens de Serviço por Manutentor',
                    font: { size: 18 }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        // Garante que o eixo X só mostre números inteiros
                        stepSize: 1
                    }
                }
            }
        }
    });
}

function renderPriorityChart(priorityData) {
    const ctx = document.getElementById('priorityChart').getContext('2d');

    const labels = Object.keys(priorityData);
    const values = Object.values(priorityData);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'OS por Prioridade',
                data: values,
                backgroundColor: [
                    '#17a2b8', // Baixa
                    '#007bff', // Média
                    '#ffc107', // Alta
                    '#fd7e14', // Crítica
                    '#dc3545'  // Emergencial
                ],
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y', // Gráfico de barras horizontais
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Ordens de Serviço por Prioridade',
                    font: { size: 18 }
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
}