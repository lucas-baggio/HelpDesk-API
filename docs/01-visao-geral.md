# Sistema de Chamados e Ordem de Serviço (HelpDesk SaaS)

## 1. Visão Geral do Projeto

### Objetivo

Desenvolver um sistema web para gerenciamento de clientes, máquinas, chamados técnicos e ordens de serviço.

O sistema deverá permitir controle operacional de atendimentos técnicos, histórico de alterações, anexos e permissões por usuário.

O projeto possui caráter de portfólio e aprendizado, mas será desenvolvido com mentalidade de produto SaaS real.

---

## 2. Objetivos Técnicos

O projeto servirá para praticar e consolidar conhecimentos em:

* Laravel
* Arquitetura MVC
* Regras de negócio
* Relacionamentos Eloquent
* Policies e autorização
* Upload de arquivos
* Queue e Jobs
* Eventos e Observers
* Testes automatizados
* Estruturação de produto real

---

## 3. Escopo Inicial

O sistema possuirá os seguintes módulos:

1. Autenticação
2. Usuários e permissões
3. Clientes
4. Máquinas
5. Chamados
6. Ordens de Serviço
7. Upload de arquivos
8. Histórico e auditoria
9. Jobs e notificações
10. Dashboard (opcional)

---

## 4. Papéis do Sistema

O sistema terá inicialmente três tipos de usuário.

### Admin

Responsável pelo gerenciamento completo do sistema.

Permissões:

* Gerenciar usuários
* Gerenciar clientes
* Gerenciar máquinas
* Gerenciar chamados
* Gerenciar OS
* Visualizar histórico
* Configurações futuras

---

### Técnico

Responsável pela execução dos serviços.

Permissões:

* Visualizar chamados
* Alterar status de chamados
* Executar OS
* Anexar arquivos
* Registrar informações técnicas

---

### Atendente

Responsável pelo atendimento inicial.

Permissões:

* Criar chamados
* Gerenciar clientes
* Visualizar informações
* Sem permissão para alterar status técnicos críticos

---

## 5. Diretrizes de Desenvolvimento

O projeto seguirá as seguintes diretrizes:

* Código limpo
* Responsabilidade única
* Regras de negócio explícitas
* Uso de Form Requests
* Uso de Policies quando necessário
* Evitar lógica excessiva em Controllers
* Commits organizados
* Documentação atualizada junto do desenvolvimento

O desenvolvimento será incremental por milestones.

---

# Regras de Negócio

## Clientes

RN-001

Cliente pode possuir múltiplas máquinas.

RN-002

CPF/CNPJ não pode ser duplicado.

RN-003

Exclusão de cliente deve ser bloqueada caso existam chamados ou máquinas vinculadas.

---

## Máquinas

RN-004

Toda máquina deve pertencer a um cliente.

RN-005

Número de série deve ser único por cliente.

---

## Chamados

RN-006

Todo chamado deve estar vinculado a cliente.

RN-007

Chamado pode opcionalmente possuir máquina associada.

RN-008

Prioridade permitida:

* baixa
* media
* alta

RN-009

Status permitidos:

* aberto
* em_andamento
* resolvido
* cancelado

RN-010

Chamado cancelado não pode ser resolvido.

RN-011

Somente técnico ou admin podem alterar status.

RN-012

Ao resolver chamado:

* registrar data_resolucao
* registrar usuário responsável

---

## Ordem de Serviço

RN-013

Chamado pode gerar apenas uma única OS.

RN-014

Criação de OS deve ocorrer dentro de transação.

RN-015

Status permitidos:

* aberta
* em_execucao
* finalizada

RN-016

OS finalizada não pode retornar para aberta.

---

## Arquivos

RN-017

Upload permitido:

* imagem
* PDF

RN-018

Arquivo removido deve ser excluído fisicamente do storage.

---

## Histórico

RN-019

Toda alteração relevante deve gerar histórico automático.

Exemplos:

* alteração de status
* alteração de valor
* alteração de responsável

Histórico deve registrar:

* usuário
* data
* alteração realizada

---

# Milestones do Projeto

## Milestone 1 — Fundação

* Projeto Laravel
* Auth
* Users + Roles
* Clientes
* Máquinas

---

## Milestone 2 — Chamados

* CRUD
* Status
* Prioridade
* Policies
* Regras de negócio

---

## Milestone 3 — Ordem de Serviço

* Conversão chamado → OS
* Transações
* Numeração

---

## Milestone 4 — Arquivos e Histórico

* Upload
* Storage
* Observer/Event
* Auditoria

---

## Milestone 5 — Jobs e Testes

* Queue
* Notificações
* Feature Tests
* Unit Tests

---

## Milestone 6 — Refinamento

* Dashboard
* Melhorias visuais
* Refatorações
* Preparação de portfólio
