<?php
// Configuração do banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "industria";

// Conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Define o período padrão (últimos 30 dias)
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d', strtotime('-730 days'));
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');

// --- Busca dados para a tabela e totais ---
$sql = "SELECT m.*, e.nome AS equipamento_nome, e.tag AS equipamento_tag
        FROM manutencoes m
        JOIN equipamentos e ON m.equipamento_id = e.id
        WHERE m.data_manutencao BETWEEN ? AND ?
        ORDER BY m.data_manutencao DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $data_inicio, $data_fim);
$stmt->execute();
$result = $stmt->get_result();

$manutencoes = [];
$custo_total = 0;
while ($row = $result->fetch_assoc()) {
    $manutencoes[] = $row;
    $custo_total += $row['custo'];
}
$stmt->close();

// --- Prepara dados para o gráfico (Custo por Mês) ---
$sql_chart = "SELECT 
                DATE_FORMAT(data_manutencao, '%Y-%m') AS mes, 
                tipo_manutencao, 
                SUM(custo) AS custo_total_tipo
              FROM manutencoes
              WHERE data_manutencao BETWEEN ? AND ?
              GROUP BY mes, tipo_manutencao
              ORDER BY mes ASC";

$stmt_chart = $conn->prepare($sql_chart);
$stmt_chart->bind_param("ss", $data_inicio, $data_fim);
$stmt_chart->execute();
$result_chart = $stmt_chart->get_result();
 
$dados_grafico = [];
while($row = $result_chart->fetch_assoc()) {
    $mes_formatado = date("M/Y", strtotime($row['mes'] . "-01"));
    if (!isset($dados_grafico[$mes_formatado])) {
        $dados_grafico[$mes_formatado] = [
            'Preventiva' => 0,
            'Corretiva' => 0,
            'Preditiva' => 0,
        ];
    }
    $dados_grafico[$mes_formatado][$row['tipo_manutencao']] = $row['custo_total_tipo'];
}

$chart_labels = array_keys($dados_grafico);
$chart_datasets = [
    'Preventiva' => ['label' => 'Preventiva', 'data' => [], 'backgroundColor' => 'rgba(54, 162, 235, 0.6)'],
    'Corretiva'  => ['label' => 'Corretiva',  'data' => [], 'backgroundColor' => 'rgba(255, 99, 132, 0.6)'],
    'Preditiva'  => ['label' => 'Preditiva',  'data' => [], 'backgroundColor' => 'rgba(255, 206, 86, 0.6)'],
];

foreach ($dados_grafico as $mes => $custos) {
    $chart_datasets['Preventiva']['data'][] = $custos['Preventiva'];
    $chart_datasets['Corretiva']['data'][] = $custos['Corretiva'];
    $chart_datasets['Preditiva']['data'][] = $custos['Preditiva'];
}

$stmt_chart->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Manutenções</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-filters {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-bottom: 30px;
            background-color: #eaf2fb;
            padding: 20px;
            border-radius: 8px;
        }
        .report-summary {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-bottom: 30px;
            text-align: center;
        }
        .summary-box {
            background-color: #eaf2fb;
            padding: 20px;
            border-radius: 8px;
            flex-grow: 1;
        }
        .summary-box h3 { margin-top: 0; color: #355c7d; }
        .summary-box p { font-size: 24px; font-weight: 700; color: #2d3e5e; margin: 0; }
        .chart-container {
            margin-bottom: 40px;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php include 'header.php'; ?>
        <h1>Relatório de Custos de Manutenção</h1>
        <!-- Filtros -->
        <form action="relatorios.php" method="GET" class="report-filters">
            <div>
                <label for="data_inicio">Data Início:</label>
                <input type="date" name="data_inicio" id="data_inicio" value="<?php echo $data_inicio; ?>">
            </div>
            <div>
                <label for="data_fim">Data Fim:</label>
                <input type="date" name="data_fim" id="data_fim" value="<?php echo $data_fim; ?>">
            </div>
            <button type="submit" class="report-filter-btn"><i class="fa fa-filter"></i> Filtrar</button>
        </form>

        <!-- Resumo -->
        <div class="report-summary">
            <div class="summary-box">
                <h3>Custo Total no Período</h3>
                <p>R$ <?php echo number_format($custo_total, 2, ',', '.'); ?></p>
            </div>
            <div class="summary-box">
                <h3>Nº de Manutenções</h3>
                <p><?php echo count($manutencoes); ?></p>
            </div>
        </div>

        <!-- Gráfico -->
        <div class="chart-container">
            <canvas id="custosChart"></canvas>
        </div>

        <!-- Tabela de Dados -->
        <h2>Detalhes das Manutenções</h2>
        <table class="table-manutencoes">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Equipamento (TAG)</th>
                    <th>Tipo</th>
                    <th>Descrição</th>
                    <th>Responsável</th>
                    <th>Custo (R$)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($manutencoes) > 0) : ?>
                    <?php foreach ($manutencoes as $manut) : ?>
                        <tr>
                            <td><?php echo date("d/m/Y", strtotime($manut['data_manutencao'])); ?></td>
                            <td><?php echo htmlspecialchars($manut['equipamento_nome'] . ' (' . $manut['equipamento_tag'] . ')'); ?></td>
                            <td><?php echo htmlspecialchars($manut['tipo_manutencao']); ?></td>
                            <td><?php echo htmlspecialchars($manut['descricao']); ?></td>
                            <td><?php echo htmlspecialchars($manut['responsavel']); ?></td>
                            <td><?php echo number_format($manut['custo'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6" class="no-results">Nenhuma manutenção encontrada para o período selecionado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        const ctx = document.getElementById('custosChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: <?php echo json_encode(array_values($chart_datasets)); ?>
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Custos de Manutenção por Tipo e Mês'
                    },
                },
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += 'R$ ' + context.parsed.y.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    </script>

</body>
</html>