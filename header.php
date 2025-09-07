<nav class="main-nav">
    <a href="index.html"><i class="fa-solid fa-plus"></i> Cadastrar Equipamento</a>
    <a href="listar_equipamentos.php"><i class="fa-solid fa-list"></i> Listar Equipamentos</a>
    <a href="relatorios.php"><i class="fa-solid fa-chart-pie"></i> Relatórios</a>
</nav>
<script>
    // Este script pode ser movido para um arquivo .js separado se preferir
    function updateTime() { const infoBar = document.getElementById('info-bar'); if (!infoBar) return; const now = new Date(); const options = { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' }; const formattedDate = now.toLocaleDateString('pt-BR', options).replace('de ', '').replace(' de', ''); infoBar.textContent = `Londrina, ${formattedDate}`; } setInterval(updateTime, 1000); document.addEventListener('DOMContentLoaded', updateTime);
</script>