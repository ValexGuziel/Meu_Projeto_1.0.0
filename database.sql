-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS sistema_os CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sistema_os;

-- Tabela de equipamentos
CREATE TABLE IF NOT EXISTS equipamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    descricao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de localizações
CREATE TABLE IF NOT EXISTS localizacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    setor VARCHAR(100) NOT NULL,
    descricao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_localizacao (nome, setor)
);

-- Tabela de tipos de manutenção
CREATE TABLE IF NOT EXISTS tipos_manutencao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL UNIQUE,
    descricao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de áreas de manutenção
CREATE TABLE IF NOT EXISTS areas_manutencao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL UNIQUE,
    responsavel VARCHAR(255) NOT NULL,
    descricao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de ordens de serviço
CREATE TABLE IF NOT EXISTS ordens_servico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_os VARCHAR(20) NOT NULL UNIQUE,
    equipamento_id INT NOT NULL,
    localizacao_id INT NOT NULL,
    tipo_manutencao_id INT NOT NULL,
    area_manutencao_id INT NOT NULL,
    prioridade VARCHAR(50) NOT NULL,
    solicitante VARCHAR(255) NOT NULL,
    data_inicial DATE NOT NULL,
    data_final DATE NULL,
    descricao_problema TEXT NOT NULL,
    status ENUM('pendente', 'em_andamento', 'concluida', 'cancelada') DEFAULT 'pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id) ON DELETE RESTRICT,
    FOREIGN KEY (localizacao_id) REFERENCES localizacoes(id) ON DELETE RESTRICT,
    FOREIGN KEY (tipo_manutencao_id) REFERENCES tipos_manutencao(id) ON DELETE RESTRICT,
    FOREIGN KEY (area_manutencao_id) REFERENCES areas_manutencao(id) ON DELETE RESTRICT
);

-- Tabela de usuários para autenticação
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserir dados iniciais para tipos de manutenção
INSERT INTO tipos_manutencao (nome, descricao) VALUES
('Preventiva', 'Manutenção programada para prevenir falhas'),
('Corretiva', 'Manutenção para corrigir falhas já ocorridas'),
('Preditiva', 'Manutenção baseada em monitoramento e análise'),
('Emergencial', 'Manutenção urgente para evitar paradas'),
('Calibração', 'Ajuste e calibração de equipamentos'),
('Inspeção', 'Verificação e inspeção de equipamentos')
ON DUPLICATE KEY UPDATE descricao = VALUES(descricao);

-- Inserir dados iniciais para áreas de manutenção
INSERT INTO areas_manutencao (nome, responsavel, descricao) VALUES
('Mecânica', 'João Silva', 'Manutenção mecânica de equipamentos'),
('Elétrica', 'Maria Santos', 'Manutenção elétrica e eletrônica'),
('Eletrônica', 'Pedro Costa', 'Manutenção de sistemas eletrônicos'),
('Instrumentação', 'Ana Oliveira', 'Manutenção de instrumentos de medição'),
('Refrigeração', 'Carlos Lima', 'Manutenção de sistemas de refrigeração'),
('Hidráulica', 'Lucia Ferreira', 'Manutenção de sistemas hidráulicos'),
('Pneumática', 'Roberto Alves', 'Manutenção de sistemas pneumáticos'),
('Civil', 'Fernanda Rocha', 'Manutenção de estruturas civis')
ON DUPLICATE KEY UPDATE responsavel = VALUES(responsavel), descricao = VALUES(descricao);

-- Inserir dados iniciais para localizações
INSERT INTO localizacoes (nome, setor, descricao) VALUES
('Sala de Controle', 'Setor A', 'Sala principal de controle de produção'),
('Linha de Produção 1', 'Setor A', 'Primeira linha de produção'),
('Linha de Produção 2', 'Setor B', 'Segunda linha de produção'),
('Almoxarifado Central', 'Almoxarifado', 'Depósito central de materiais'),
('Sala de Máquinas', 'Setor C', 'Sala com equipamentos principais'),
('Escritório Administrativo', 'Administrativo', 'Escritório da administração'),
('Laboratório de Qualidade', 'Laboratório', 'Laboratório para testes de qualidade'),
('Oficina de Manutenção', 'Manutenção', 'Oficina principal de manutenção')
ON DUPLICATE KEY UPDATE descricao = VALUES(descricao);

-- Inserir dados iniciais para equipamentos
INSERT INTO equipamentos (nome, codigo, descricao) VALUES
('Compressor Principal', 'COMP001', 'Compressor de ar principal da fábrica'),
('Bomba Hidráulica', 'BOMB001', 'Bomba hidráulica da linha 1'),
('Motor Elétrico 50HP', 'MOT001', 'Motor elétrico principal'),
('Painel de Controle', 'PAIN001', 'Painel de controle da produção'),
('Sistema de Refrigeração', 'REF001', 'Sistema de refrigeração industrial'),
('Transformador', 'TRANS001', 'Transformador de energia'),
('Ventilador Industrial', 'VENT001', 'Ventilador de exaustão'),
('Esteira Transportadora', 'EST001', 'Esteira da linha de produção')
ON DUPLICATE KEY UPDATE descricao = VALUES(descricao);
