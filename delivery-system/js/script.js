const API_BASE = 'php/';

// Funções principais
async function carregarEstatisticas() {
    try {
        const response = await fetch(`${API_BASE}pedidos.php?action=estatisticas`);
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('total-usuarios').textContent = data.data.total_usuarios;
            document.getElementById('total-pedidos').textContent = data.data.total_pedidos;
            document.getElementById('total-produtos').textContent = data.data.total_produtos;
            document.getElementById('total-entregadores').textContent = data.data.total_entregadores;
        }
    } catch (error) {
        mostrarNotificacao('Erro ao carregar estatísticas', 'error');
    }
}

async function carregarPedidos() {
    mostrarLoading(true);
    try {
        const response = await fetch(`${API_BASE}pedidos.php?action=listar`);
        const data = await response.json();
        
        if (data.success) {
            const tbody = document.getElementById('pedidos-body');
            tbody.innerHTML = '';
            
            data.data.forEach(pedido => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>#${pedido.pedido_id}</td>
                    <td>${pedido.cliente}</td>
                    <td>R$ ${parseFloat(pedido.total).toFixed(2)}</td>
                    <td class="status-${pedido.status}">${formatarStatus(pedido.status)}</td>
                    <td>${formatarData(pedido.created_at)}</td>
                    <td>${pedido.total_itens}</td>
                `;
                tbody.appendChild(tr);
            });
        }
    } catch (error) {
        mostrarNotificacao('Erro ao carregar pedidos', 'error');
    } finally {
        mostrarLoading(false);
    }
}

async function carregarUsuarios() {
    try {
        const response = await fetch(`${API_BASE}usuarios.php?action=listar`);
        const data = await response.json();
        
        if (data.success) {
            const tbody = document.getElementById('usuarios-body');
            tbody.innerHTML = '';
            
            data.data.forEach(usuario => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${usuario.id}</td>
                    <td>${usuario.nome}</td>
                    <td>${usuario.email}</td>
                    <td>${usuario.telefone}</td>
                    <td>${usuario.endereco}</td>
                    <td>${formatarData(usuario.created_at)}</td>
                `;
                tbody.appendChild(tr);
            });
        }
    } catch (error) {
        mostrarNotificacao('Erro ao carregar usuários', 'error');
    }
}

async function carregarProdutos() {
    try {
        const response = await fetch(`${API_BASE}produtos.php?action=listar`);
        const data = await response.json();
        
        if (data.success) {
            const tbody = document.getElementById('produtos-body');
            tbody.innerHTML = '';
            
            data.data.forEach(produto => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${produto.id}</td>
                    <td>${produto.nome}</td>
                    <td>${produto.descricao}</td>
                    <td>R$ ${parseFloat(produto.preco).toFixed(2)}</td>
                    <td>${produto.categoria}</td>
                    <td>${produto.disponivel ? 'Sim' : 'Não'}</td>
                `;
                tbody.appendChild(tr);
            });
        }
    } catch (error) {
        mostrarNotificacao('Erro ao carregar produtos', 'error');
    }
}

async function carregarEntregadores() {
    try {
        const response = await fetch(`${API_BASE}entregadores.php?action=listar`);
        const data = await response.json();
        
        if (data.success) {
            const tbody = document.getElementById('entregadores-body');
            tbody.innerHTML = '';
            
            data.data.forEach(entregador => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${entregador.id}</td>
                    <td>${entregador.nome}</td>
                    <td>${entregador.telefone}</td>
                    <td>${entregador.veiculo}</td>
                    <td>${entregador.disponivel ? 'Sim' : 'Não'}</td>
                `;
                tbody.appendChild(tr);
            });
        }
    } catch (error) {
        mostrarNotificacao('Erro ao carregar entregadores', 'error');
    }
}

async function simularPedidos() {
    mostrarLoading(true);
    try {
        const response = await fetch(`${API_BASE}pedidos.php?action=simular`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ numero_pedidos: 5 })
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarNotificacao(`${data.data.total_pedidos} pedidos simulados com sucesso!`, 'success');
            carregarTodosDados();
        } else {
            mostrarNotificacao('Erro ao simular pedidos', 'error');
        }
    } catch (error) {
        mostrarNotificacao('Erro ao simular pedidos', 'error');
    } finally {
        mostrarLoading(false);
    }
}

// Funções de UI
function abrirTab(tabName) {
    // Esconder todas as tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remover active de todos os botões
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Mostrar tab selecionada
    document.getElementById(`tab-${tabName}`).classList.add('active');
    
    // Ativar botão
    event.target.classList.add('active');
    
    // Carregar dados específicos da tab se necessário
    switch(tabName) {
        case 'pedidos':
            carregarPedidos();
            break;
        case 'usuarios':
            carregarUsuarios();
            break;
        case 'produtos':
            carregarProdutos();
            break;
        case 'entregadores':
            carregarEntregadores();
            break;
    }
}

function mostrarFormUsuario() {
    fecharFormularios();
    document.getElementById('form-usuario').style.display = 'block';
}

function mostrarFormProduto() {
    fecharFormularios();
    document.getElementById('form-produto').style.display = 'block';
}

function fecharFormularios() {
    document.querySelectorAll('.form-container').forEach(form => {
        form.style.display = 'none';
    });
}

function mostrarLoading(show) {
    document.getElementById('loading').style.display = show ? 'flex' : 'none';
}

function mostrarNotificacao(mensagem, tipo = 'info') {
    const notifications = document.getElementById('notifications');
    const notification = document.createElement('div');
    notification.className = `notification ${tipo}`;
    notification.textContent = mensagem;
    
    notifications.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Funções utilitárias
function formatarStatus(status) {
    const statusMap = {
        'pendente': 'Pendente',
        'preparando': 'Preparando',
        'saiu_entrega': 'Saiu para Entrega',
        'entregue': 'Entregue',
        'cancelado': 'Cancelado'
    };
    return statusMap[status] || status;
}

function formatarData(dataString) {
    const data = new Date(dataString);
    return data.toLocaleString('pt-BR');
}

// Função para carregar todos os dados
async function carregarTodosDados() {
    await Promise.all([
        carregarEstatisticas(),
        carregarPedidos(),
        carregarUsuarios(),
        carregarProdutos(),
        carregarEntregadores()
    ]);
}

// Event Listeners
document.getElementById('usuarioForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch(`${API_BASE}usuarios.php?action=cadastrar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            mostrarNotificacao('Usuário cadastrado com sucesso!', 'success');
            this.reset();
            fecharFormularios();
            carregarTodosDados();
        } else {
            mostrarNotificacao(result.message || 'Erro ao cadastrar usuário', 'error');
        }
    } catch (error) {
        mostrarNotificacao('Erro ao cadastrar usuário', 'error');
    }
});

document.getElementById('produtoForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    data.preco = parseFloat(data.preco);
    
    try {
        const response = await fetch(`${API_BASE}produtos.php?action=cadastrar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            mostrarNotificacao('Produto cadastrado com sucesso!', 'success');
            this.reset();
            fecharFormularios();
            carregarTodosDados();
        } else {
            mostrarNotificacao(result.message || 'Erro ao cadastrar produto', 'error');
        }
    } catch (error) {
        mostrarNotificacao('Erro ao cadastrar produto', 'error');
    }
});

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    carregarTodosDados();
});
// Event Listener para formulário de usuário (VERSÃO CORRIGIDA)
document.getElementById('usuarioForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    console.log('=== INICIANDO CADASTRO DE USUÁRIO ===');
    
    // Coletar dados do formulário
    const formData = {
        nome: document.getElementById('nome').value.trim(),
        email: document.getElementById('email').value.trim(),
        telefone: document.getElementById('telefone').value.trim(),
        endereco: document.getElementById('endereco').value.trim()
    };
    
    console.log('Dados do formulário:', formData);
    
    // Validação básica no front-end
    if (!formData.nome) {
        mostrarNotificacao('Por favor, informe o nome', 'error');
        return;
    }
    
    if (!formData.email) {
        mostrarNotificacao('Por favor, informe o email', 'error');
        return;
    }
    
    if (!formData.telefone) {
        mostrarNotificacao('Por favor, informe o telefone', 'error');
        return;
    }
    
    if (!formData.endereco) {
        mostrarNotificacao('Por favor, informe o endereço', 'error');
        return;
    }
    
    // Validar email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(formData.email)) {
        mostrarNotificacao('Por favor, informe um email válido', 'error');
        return;
    }
    
    mostrarLoading(true);
    
    try {
        console.log('Enviando requisição para:', `${API_BASE}usuarios.php?action=cadastrar`);
        
        const response = await fetch(`${API_BASE}usuarios.php?action=cadastrar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        console.log('Status da resposta:', response.status);
        
        const result = await response.json();
        console.log('Resposta do servidor:', result);
        
        if (result.success) {
            mostrarNotificacao('Usuário cadastrado com sucesso!', 'success');
            this.reset();
            fecharFormularios();
            
            // Recarregar dados
            await carregarUsuarios();
            await carregarEstatisticas();
            
            console.log('Usuário cadastrado e dados atualizados!');
            
        } else {
            const mensagemErro = result.message || 'Erro desconhecido ao cadastrar usuário';
            console.error('Erro no cadastro:', mensagemErro);
            
            // Verifica se é uma mensagem sobre email já cadastrado
            if (mensagemErro.toLowerCase().includes('email já está cadastrado')) {
                mostrarNotificacao(mensagemErro, 'success');
            } else {
                mostrarNotificacao(mensagemErro, 'error');
            }
        }
        
    } catch (error) {
        console.error('Erro de conexão:', error);
        mostrarNotificacao('Erro de conexão com o servidor', 'error');
    } finally {
        mostrarLoading(false);
    }
});

// Função carregarUsuarios atualizada
async function carregarUsuarios() {
    try {
        console.log('Carregando usuários...');
        const response = await fetch(`${API_BASE}usuarios.php?action=listar`);
        const data = await response.json();
        
        console.log('Resposta da listagem:', data);
        
        if (data.success) {
            const tbody = document.getElementById('usuarios-body');
            tbody.innerHTML = '';
            
            console.log('Total de usuários:', data.data.length);
            
            if (data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Nenhum usuário cadastrado</td></tr>';
                return;
            }
            
            data.data.forEach(usuario => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${usuario.id}</td>
                    <td>${usuario.nome}</td>
                    <td>${usuario.email}</td>
                    <td>${usuario.telefone}</td>
                    <td>${usuario.endereco}</td>
                    <td>${formatarData(usuario.created_at)}</td>
                `;
                tbody.appendChild(tr);
            });
            
            console.log('Tabela de usuários atualizada');
        } else {
            console.error('Erro ao carregar usuários:', data.message);
        }
    } catch (error) {
        console.error('Erro ao carregar usuários:', error);
        mostrarNotificacao('Erro ao carregar usuários', 'error');
    }
}