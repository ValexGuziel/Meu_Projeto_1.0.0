# Sistema de Cadastro de Ordem de Serviço

Sistema web para cadastro de Ordens de Serviço (OS) com modais para cadastrar equipamentos, localizações, tipos de manutenção e áreas de manutenção.

## Funcionalidades

- ✅ Cadastro de Ordens de Serviço com número único automático
- ✅ Modais para cadastrar dados auxiliares
- ✅ Gerenciamento completo de OS (visualizar, editar, excluir)
- ✅ Sistema de pesquisa e filtros avançados
- ✅ Paginação para melhor performance
- ✅ Relatórios imprimíveis
- ✅ Integração com banco de dados MySQL
- ✅ Interface responsiva e moderna
- ✅ Design adaptativo para mobile, tablet e desktop
- ✅ Otimização para dispositivos touch
- ✅ Layout otimizado para impressão
- ✅ Validação de formulários
- ✅ Feedback visual para o usuário
- ✅ Numeração automática no formato 0001-dd-mm-yy
- ✅ Relacionamentos entre tabelas

## Requisitos

- XAMPP (Apache + MySQL + PHP)
- Navegador web moderno
- PHP 7.4 ou superior
- Dispositivo com tela de 320px ou mais (smartphones, tablets, desktops)

## Instalação

### 1. Configurar XAMPP

1. Baixe e instale o XAMPP: https://www.apachefriends.org/
2. Inicie o Apache e MySQL no painel de controle do XAMPP

### 2. Configurar o Projeto

1. Clone ou baixe este projeto
2. Coloque os arquivos na pasta `htdocs` do XAMPP:
   ```
   C:\xampp\htdocs\meu-projeto\
   ```

### 3. Configurar o Banco de Dados

1. Abra o phpMyAdmin: http://localhost/phpmyadmin
2. Crie um novo banco de dados chamado `sistema_os`
3. Importe o arquivo `database.sql` ou execute o SQL manualmente

**Ou via linha de comando:**

```bash
mysql -u root -p < database.sql
```

### 4. Configurar Conexão

1. Verifique se as configurações no arquivo `api/config.php` estão corretas:
   ```php
   $host = 'localhost';
   $dbname = 'sistema_os';
   $username = 'root';
   $password = '';
   ```

### 5. Acessar o Sistema

1. Abra seu navegador
2. Acesse: http://localhost/meu-projeto/
3. Para testar a responsividade: http://localhost/meu-projeto/teste_responsividade.html

## Estrutura do Projeto

```
meu-projeto/
├── index.html              # Página principal
├── style.css               # Estilos CSS
├── script.js               # JavaScript
├── database.sql            # Script do banco de dados
├── README.md               # Este arquivo
└── api/                    # API PHP
    ├── config.php          # Configuração do banco
    ├── get_equipamentos.php
    ├── save_equipamento.php
    ├── get_localizacoes.php
    ├── save_localizacao.php
    ├── get_tipos_manutencao.php
    ├── save_tipo_manutencao.php
    ├── get_areas_manutencao.php
    ├── save_area_manutencao.php
    ├── get_next_os_number.php
    ├── save_ordem_servico.php
    ├── get_ordens_servico.php
    ├── get_os_by_id.php
    ├── update_ordem_servico.php
    └── delete_ordem_servico.php
```

## Como Usar

### Cadastrar Equipamento

1. Clique no botão "+" ao lado do campo "Equipamento"
2. Preencha o nome, código e descrição
3. Clique em "Salvar"

### Cadastrar Localização

1. Clique no botão "+" ao lado do campo "Localização"
2. Preencha o nome, setor e descrição
3. Clique em "Salvar"

### Cadastrar Tipo de Manutenção

1. Clique no botão "+" ao lado do campo "Tipo de Manutenção"
2. Preencha o nome e descrição
3. Clique em "Salvar"

### Cadastrar Área de Manutenção

1. Clique no botão "+" ao lado do campo "Área de Manutenção"
2. Preencha o nome, responsável e descrição
3. Clique em "Salvar"

### Cadastrar Ordem de Serviço

1. O número da OS é gerado automaticamente no formato 0001-dd-mm-yy
2. Preencha todos os campos obrigatórios
3. Selecione equipamento, localização, tipo e área de manutenção
4. Clique em "Adicionar" para salvar a OS

### Gerenciar Ordens de Serviço

1. Clique no botão "Gerenciar OS" para abrir o modal de gerenciamento
2. Use a barra de pesquisa para buscar por número da OS, equipamento ou solicitante
3. Aplique filtros por status, prioridade ou data
4. Use os botões de ação:
   - 👁️ **Visualizar**: Ver detalhes completos da OS
   - ✏️ **Editar**: Modificar dados da OS
   - 🖨️ **Imprimir**: Imprimir OS individual em formato profissional
   - 🗑️ **Excluir**: Remover a OS (com confirmação)
5. Use a paginação para navegar entre as páginas
6. Clique em "Imprimir" (botão inferior) para gerar relatórios gerais

## Tabelas do Banco de Dados

### equipamentos

- `id` - Chave primária
- `nome` - Nome do equipamento
- `codigo` - Código único do equipamento
- `descricao` - Descrição do equipamento

### localizacoes

- `id` - Chave primária
- `nome` - Nome da localização
- `setor` - Setor da localização
- `descricao` - Descrição da localização

### tipos_manutencao

- `id` - Chave primária
- `nome` - Nome do tipo de manutenção
- `descricao` - Descrição do tipo

### areas_manutencao

- `id` - Chave primária
- `nome` - Nome da área
- `responsavel` - Responsável pela área
- `descricao` - Descrição da área

### ordens_servico

- `id` - Chave primária
- `numero_os` - Número único da OS (formato: 0001-dd-mm-yy)
- `equipamento_id` - ID do equipamento (FK)
- `localizacao_id` - ID da localização (FK)
- `tipo_manutencao_id` - ID do tipo de manutenção (FK)
- `area_manutencao_id` - ID da área de manutenção (FK)
- `prioridade` - Prioridade da OS
- `solicitante` - Nome do solicitante
- `data_inicial` - Data inicial
- `data_final` - Data final (opcional)
- `descricao_problema` - Descrição do problema
- `status` - Status da OS (pendente, em_andamento, concluida, cancelada)

## Responsividade

O sistema é totalmente responsivo e se adapta automaticamente a diferentes tamanhos de tela:

### 📱 Dispositivos Móveis (até 480px)

- Formulário em coluna única
- Botões em largura total
- Tabela com colunas menos importantes ocultas
- Modais otimizados para touch
- Botões com tamanho mínimo de 44px para facilitar o toque

### 📱 Tablets (481px - 768px)

- Layout adaptativo com grid responsivo
- Formulário em múltiplas colunas quando possível
- Modais em tamanho médio
- Tabela com scroll horizontal quando necessário

### 💻 Desktop (769px+)

- Layout completo com todas as funcionalidades
- Formulário em múltiplas colunas
- Modais grandes para melhor visualização
- Tabela com todas as colunas visíveis

### 🖨️ Impressão

- Layout otimizado para impressão
- Elementos de interface ocultos
- Quebras de página controladas

## Personalização

### Cores

```css
:root {
  --primary-blue: #1976d2;
  --accent-orange: #f57c00;
  --bg-color: #eef2f5;
  /* ... */
}
```

### Campos

Para adicionar novos campos, edite o arquivo `index.html` e adicione os estilos correspondentes no `style.css`.

## Suporte

Para dúvidas ou problemas:

1. Verifique se o XAMPP está rodando
2. Verifique se o banco de dados foi criado corretamente
3. Verifique os logs de erro do Apache/PHP
4. Verifique o console do navegador para erros JavaScript

## Licença

Este projeto é de uso livre para fins educacionais e comerciais.
# Meu_Projeto
