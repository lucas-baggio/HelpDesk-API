# Sistema de Chamados e Ordem de Serviço (HelpDesk SaaS)

## 1. Visão Geral do Projeto

### Objetivo

Desenvolver um sistema web para gerenciamento de clientes, máquinas, chamados técnicos e ordens de serviço.

O sistema permite controle operacional de atendimentos técnicos, histórico de alterações, anexos e permissões por usuário.

O projeto possui caráter de portfólio e aprendizado, mas é desenvolvido com mentalidade de produto SaaS real.

---

## 2. Objetivos Técnicos

O projeto serve para praticar e consolidar conhecimentos em:

* Laravel (Pragmatic Modular Architecture)
* API-only REST + JWT
* Regras de negócio explícitas em Actions
* Relacionamentos Eloquent
* Policies e autorização por roles
* Upload de arquivos
* Queue e Jobs
* Eventos e auditoria automática
* Testes automatizados (PHPUnit)

---

## 3. Escopo

| Módulo | Status |
|--------|--------|
| Autenticação (JWT) | ✅ Implementado |
| Usuários e permissões | ✅ Implementado (create, update) |
| Clientes | ✅ Implementado (CRUD completo) |
| Máquinas | ✅ Implementado (CRUD completo) |
| Chamados | ✅ Implementado (CRUD + transições de status) |
| Ordens de Serviço | ✅ Implementado (CRUD completo + transições de status) |
| Upload de arquivos | ✅ Implementado (upload, listagem, download, exclusão) |
| Histórico e auditoria | 🔜 Planejado |
| Jobs e notificações | 🔜 Planejado |

---

## 4. Papéis do Sistema

O sistema possui três tipos de usuário.

### Admin

Responsável pelo gerenciamento completo do sistema.

Permissões:

* Gerenciar usuários (create, update)
* Gerenciar clientes (CRUD)
* Gerenciar máquinas (CRUD)
* Gerenciar chamados e OS
* Visualizar histórico

---

### Técnico (`tecnico`)

Responsável pela execução dos serviços.

Permissões:

* Visualizar clientes e máquinas
* Visualizar chamados
* Alterar status técnicos de chamados
* Executar e atualizar OS
* Anexar arquivos
* Registrar informações técnicas

---

### Atendente (`atendente`)

Responsável pelo atendimento inicial.

Permissões:

* Criar e editar clientes
* Criar e editar máquinas
* Criar chamados
* Visualizar informações gerais
* **Não pode** alterar status técnicos críticos nem finalizar OS

---

## 5. Regras de Negócio

As regras de negócio autoritativas estão em [`02-business-rules.md`](02-business-rules.md) com identificadores estáveis (`RN-001` a `RN-033`).

Resumo por domínio:

| Domínio | Regras |
|---------|--------|
| Usuários e roles | RN-001 – RN-004 |
| Clientes | RN-005 – RN-008 |
| Máquinas | RN-009 – RN-012 |
| Chamados | RN-013 – RN-020 |
| Ordens de Serviço | RN-021 – RN-025 |
| Arquivos | RN-026 – RN-029 |
| Histórico e auditoria | RN-030 – RN-033 |

---

## 6. Diretrizes de Desenvolvimento

* Código limpo, responsabilidade única
* Regras de negócio explícitas em Actions (nunca em Controllers)
* Form Requests para validação e autorização HTTP
* Policies para controle de permissões por role
* Transações em operações multi-step
* Commits organizados por domínio
* Documentação atualizada junto do desenvolvimento
* 100% de cobertura via testes automatizados dos fluxos implementados

---

## 7. Milestones

### Milestone 1 — Fundação ✅ Concluído

* Projeto Laravel com PostgreSQL
* JWT Authentication (`/auth/login`, `/auth/me`)
* Comando `make:domain` para scaffolding
* Layer compartilhado de respostas API (`ApiResponse`, `ApiExceptionRenderer`)
* Módulo User (create, update) com roles e policies
* Módulo Client (CRUD completo + paginação)
* Módulo Machine (CRUD completo + paginação, vinculado a Client)

---

### Milestone 2 — Chamados 🔜

* CRUD de chamados
* Status e prioridade
* Transições de status com policies
* Regras RN-013 a RN-020

---

### Milestone 3 — Ordem de Serviço ✅

* Conversão chamado → OS (transação) ✅
* Numeração sequencial automática (`OS-00001`) ✅
* Lifecycle control (RN-021 – RN-025) ✅

---

### Milestone 4 — Arquivos e Histórico

* Upload (imagem e PDF) ✅
* Remoção física do storage ✅
* Observer/Event para auditoria automática

---

### Milestone 5 — Jobs e Notificações

* Queue workers
* Notificações por email/push em eventos-chave

---

### Milestone 6 — Refinamento

* Cobertura de testes ampliada
* OpenAPI / Swagger
* Preparação de portfólio
