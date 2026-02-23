<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PedidosController;
use App\Http\Controllers\PedidosConfiguracoesController;
use App\Http\Controllers\ClientesConfiguracoesController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\ProdutosController;
use App\Http\Controllers\DashboardController;

// Página inicial
Route::get('/', function () {
    return inertia('Welcome');
});

// Autenticação
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rotas autenticadas
Route::prefix('{empresa}')->middleware('auth')->group(function () {

    // Página inicial da empresa (lista de pedidos)
    Route::get('', function ($empresa) {
        return inertia('Pedidos/Index');
    })->where('empresa', '[0-9]+');

    // Recursos principais
    Route::get('pedidos/configuracoes', function ($empresa) {
        return redirect("/{$empresa}/pedidos/configuracoes/campos_extras");
    })->where(['empresa' => '[0-9]+']);

    Route::get('pedidos/configuracoes/campos_extras', [PedidosConfiguracoesController::class, 'camposExtras'])
        ->where(['empresa' => '[0-9]+'])
        ->name('pedidos.config.campos_extras');
    Route::post('pedidos/configuracoes/campos_extras', [PedidosConfiguracoesController::class, 'storeCampoExtra'])
        ->where(['empresa' => '[0-9]+'])
        ->name('pedidos.config.campos_extras.store');
    Route::put('pedidos/configuracoes/campos_extras/{campo}', [PedidosConfiguracoesController::class, 'updateCampoExtra'])
        ->where(['empresa' => '[0-9]+', 'campo' => '[0-9]+'])
        ->name('pedidos.config.campos_extras.update');
    Route::delete('pedidos/configuracoes/campos_extras/{campo}', [PedidosConfiguracoesController::class, 'destroyCampoExtra'])
        ->where(['empresa' => '[0-9]+', 'campo' => '[0-9]+'])
        ->name('pedidos.config.campos_extras.destroy');

    Route::get('pedidos/configuracoes/status_pedido', [PedidosConfiguracoesController::class, 'statusPedido'])
        ->where(['empresa' => '[0-9]+'])
        ->name('pedidos.config.status');
    Route::post('pedidos/configuracoes/status_pedido', [PedidosConfiguracoesController::class, 'storeStatusPedido'])
        ->where(['empresa' => '[0-9]+'])
        ->name('pedidos.config.status.store');
    Route::put('pedidos/configuracoes/status_pedido/{status}', [PedidosConfiguracoesController::class, 'updateStatusPedido'])
        ->where(['empresa' => '[0-9]+', 'status' => '[0-9]+'])
        ->name('pedidos.config.status.update');
    Route::delete('pedidos/configuracoes/status_pedido/{status}', [PedidosConfiguracoesController::class, 'destroyStatusPedido'])
        ->where(['empresa' => '[0-9]+', 'status' => '[0-9]+'])
        ->name('pedidos.config.status.destroy');

    Route::get('pedidos/configuracoes/tipo_pedido', [PedidosConfiguracoesController::class, 'tipoPedido'])
        ->where(['empresa' => '[0-9]+'])
        ->name('pedidos.config.tipo');
    Route::post('pedidos/configuracoes/tipo_pedido', [PedidosConfiguracoesController::class, 'storeTipoPedido'])
        ->where(['empresa' => '[0-9]+'])
        ->name('pedidos.config.tipo.store');
    Route::put('pedidos/configuracoes/tipo_pedido/{tipo}', [PedidosConfiguracoesController::class, 'updateTipoPedido'])
        ->where(['empresa' => '[0-9]+', 'tipo' => '[0-9]+'])
        ->name('pedidos.config.tipo.update');
    Route::delete('pedidos/configuracoes/tipo_pedido/{tipo}', [PedidosConfiguracoesController::class, 'destroyTipoPedido'])
        ->where(['empresa' => '[0-9]+', 'tipo' => '[0-9]+'])
        ->name('pedidos.config.tipo.destroy');

    Route::get('pedidos/configuracoes/geral', [PedidosConfiguracoesController::class, 'geral'])
        ->where(['empresa' => '[0-9]+'])
        ->name('pedidos.config.geral');
    Route::post('pedidos/configuracoes/geral', [PedidosConfiguracoesController::class, 'salvarGeral'])
        ->where(['empresa' => '[0-9]+'])
        ->name('pedidos.config.geral.save');

    Route::post('pedidos/export-config', [PedidosController::class, 'salvarConfiguracaoExportacao'])
        ->where(['empresa' => '[0-9]+'])
        ->name('pedidos.export-config');
    Route::get('pedidos/{pedido}/export', [PedidosController::class, 'exportar'])
        ->where(['empresa' => '[0-9]+', 'pedido' => '[0-9]+'])
        ->name('pedidos.export');

    Route::resource('pedidos', PedidosController::class)->where(['empresa' => '[0-9]+']);

    Route::get('clientes/configuracoes', function ($empresa) {
        return redirect("/{$empresa}/clientes/configuracoes/campos_extras");
    })->where(['empresa' => '[0-9]+']);

    Route::get('clientes/configuracoes/campos_extras', [ClientesConfiguracoesController::class, 'camposExtras'])
        ->where(['empresa' => '[0-9]+'])
        ->name('clientes.config.campos_extras');
    Route::post('clientes/configuracoes/campos_extras', [ClientesConfiguracoesController::class, 'storeCampoExtra'])
        ->where(['empresa' => '[0-9]+'])
        ->name('clientes.config.campos_extras.store');
    Route::put('clientes/configuracoes/campos_extras/{campo}', [ClientesConfiguracoesController::class, 'updateCampoExtra'])
        ->where(['empresa' => '[0-9]+', 'campo' => '[0-9]+'])
        ->name('clientes.config.campos_extras.update');
    Route::delete('clientes/configuracoes/campos_extras/{campo}', [ClientesConfiguracoesController::class, 'destroyCampoExtra'])
        ->where(['empresa' => '[0-9]+', 'campo' => '[0-9]+'])
        ->name('clientes.config.campos_extras.destroy');

    Route::get('clientes/configuracoes/tags', [ClientesConfiguracoesController::class, 'tags'])
        ->where(['empresa' => '[0-9]+'])
        ->name('clientes.config.tags');
    Route::post('clientes/configuracoes/tags', [ClientesConfiguracoesController::class, 'storeTag'])
        ->where(['empresa' => '[0-9]+'])
        ->name('clientes.config.tags.store');
    Route::put('clientes/configuracoes/tags/{id}', [ClientesConfiguracoesController::class, 'updateTag'])
        ->where(['empresa' => '[0-9]+', 'id' => '[0-9]+'])
        ->name('clientes.config.tags.update');
    Route::delete('clientes/configuracoes/tags/{id}', [ClientesConfiguracoesController::class, 'destroyTag'])
        ->where(['empresa' => '[0-9]+', 'id' => '[0-9]+'])
        ->name('clientes.config.tags.destroy');

    Route::get('clientes/configuracoes/segmentos', [ClientesConfiguracoesController::class, 'segmentos'])
        ->where(['empresa' => '[0-9]+'])
        ->name('clientes.config.segmentos');
    Route::post('clientes/configuracoes/segmentos', [ClientesConfiguracoesController::class, 'storeSegmento'])
        ->where(['empresa' => '[0-9]+'])
        ->name('clientes.config.segmentos.store');
    Route::put('clientes/configuracoes/segmentos/{id}', [ClientesConfiguracoesController::class, 'updateSegmento'])
        ->where(['empresa' => '[0-9]+', 'id' => '[0-9]+'])
        ->name('clientes.config.segmentos.update');
    Route::delete('clientes/configuracoes/segmentos/{id}', [ClientesConfiguracoesController::class, 'destroySegmento'])
        ->where(['empresa' => '[0-9]+', 'id' => '[0-9]+'])
        ->name('clientes.config.segmentos.destroy');

    Route::get('clientes/configuracoes/redes', [ClientesConfiguracoesController::class, 'redes'])
        ->where(['empresa' => '[0-9]+'])
        ->name('clientes.config.redes');
    Route::post('clientes/configuracoes/redes', [ClientesConfiguracoesController::class, 'storeRede'])
        ->where(['empresa' => '[0-9]+'])
        ->name('clientes.config.redes.store');
    Route::put('clientes/configuracoes/redes/{id}', [ClientesConfiguracoesController::class, 'updateRede'])
        ->where(['empresa' => '[0-9]+', 'id' => '[0-9]+'])
        ->name('clientes.config.redes.update');
    Route::delete('clientes/configuracoes/redes/{id}', [ClientesConfiguracoesController::class, 'destroyRede'])
        ->where(['empresa' => '[0-9]+', 'id' => '[0-9]+'])
        ->name('clientes.config.redes.destroy');

    Route::get('clientes/configuracoes/excecoes_fiscais', [ClientesConfiguracoesController::class, 'excecoesFiscais'])
        ->where(['empresa' => '[0-9]+'])
        ->name('clientes.config.excecoes_fiscais');
    Route::post('clientes/configuracoes/excecoes_fiscais', [ClientesConfiguracoesController::class, 'storeExcecaoFiscal'])
        ->where(['empresa' => '[0-9]+'])
        ->name('clientes.config.excecoes_fiscais.store');
    Route::put('clientes/configuracoes/excecoes_fiscais/{id}', [ClientesConfiguracoesController::class, 'updateExcecaoFiscal'])
        ->where(['empresa' => '[0-9]+', 'id' => '[0-9]+'])
        ->name('clientes.config.excecoes_fiscais.update');
    Route::delete('clientes/configuracoes/excecoes_fiscais/{id}', [ClientesConfiguracoesController::class, 'destroyExcecaoFiscal'])
        ->where(['empresa' => '[0-9]+', 'id' => '[0-9]+'])
        ->name('clientes.config.excecoes_fiscais.destroy');

    Route::get('clientes/configuracoes/resultados_atendimentos', [ClientesConfiguracoesController::class, 'resultadosAtendimentos'])
        ->where(['empresa' => '[0-9]+'])
        ->name('clientes.config.resultados');
    Route::post('clientes/configuracoes/resultados_atendimentos', [ClientesConfiguracoesController::class, 'storeResultadoAtendimento'])
        ->where(['empresa' => '[0-9]+'])
        ->name('clientes.config.resultados.store');
    Route::put('clientes/configuracoes/resultados_atendimentos/{id}', [ClientesConfiguracoesController::class, 'updateResultadoAtendimento'])
        ->where(['empresa' => '[0-9]+', 'id' => '[0-9]+'])
        ->name('clientes.config.resultados.update');
    Route::delete('clientes/configuracoes/resultados_atendimentos/{id}', [ClientesConfiguracoesController::class, 'destroyResultadoAtendimento'])
        ->where(['empresa' => '[0-9]+', 'id' => '[0-9]+'])
        ->name('clientes.config.resultados.destroy');

    Route::get('clientes/configuracoes/motivos_bloqueio', [ClientesConfiguracoesController::class, 'motivosBloqueio'])
        ->where(['empresa' => '[0-9]+'])
        ->name('clientes.config.motivos');
    Route::post('clientes/configuracoes/motivos_bloqueio', [ClientesConfiguracoesController::class, 'storeMotivoBloqueio'])
        ->where(['empresa' => '[0-9]+'])
        ->name('clientes.config.motivos.store');
    Route::put('clientes/configuracoes/motivos_bloqueio/{id}', [ClientesConfiguracoesController::class, 'updateMotivoBloqueio'])
        ->where(['empresa' => '[0-9]+', 'id' => '[0-9]+'])
        ->name('clientes.config.motivos.update');
    Route::delete('clientes/configuracoes/motivos_bloqueio/{id}', [ClientesConfiguracoesController::class, 'destroyMotivoBloqueio'])
        ->where(['empresa' => '[0-9]+', 'id' => '[0-9]+'])
        ->name('clientes.config.motivos.destroy');

    Route::get('clientes/configuracoes/geral', [ClientesConfiguracoesController::class, 'geral'])
        ->where(['empresa' => '[0-9]+'])
        ->name('clientes.config.geral');
    Route::post('clientes/configuracoes/geral', [ClientesConfiguracoesController::class, 'salvarGeral'])
        ->where(['empresa' => '[0-9]+'])
        ->name('clientes.config.geral.save');

    Route::get('clientes/vinculos-permissoes', [ClientesController::class, 'vinculosPermissoes'])
        ->where(['empresa' => '[0-9]+'])
        ->name('clientes.vinculos');

    Route::resource('clientes', ClientesController::class)->where(['empresa' => '[0-9]+']);

    Route::post('produtos/importar-fotos', [ProdutosController::class, 'importarFotos'])
        ->where(['empresa' => '[0-9]+'])
        ->name('produtos.importar_fotos');
    Route::post('produtos/{produto}/imagens', [ProdutosController::class, 'storeImagemProduto'])
        ->where(['empresa' => '[0-9]+', 'produto' => '[0-9]+'])
        ->name('produtos.imagens.store');
    Route::delete('produtos/{produto}/imagens/{imagem}', [ProdutosController::class, 'destroyImagemProduto'])
        ->where(['empresa' => '[0-9]+', 'produto' => '[0-9]+', 'imagem' => '[0-9]+'])
        ->name('produtos.imagens.destroy');
    Route::put('produtos/{produto}/imagens/ordenacao', [ProdutosController::class, 'updateOrdenacaoImagensProduto'])
        ->where(['empresa' => '[0-9]+', 'produto' => '[0-9]+'])
        ->name('produtos.imagens.ordenacao.update');
    Route::put('produtos/tabelas-precos/{tabela}', [ProdutosController::class, 'updateTabelaPreco'])
        ->where(['empresa' => '[0-9]+', 'tabela' => '[0-9]+'])
        ->name('produtos.tabelas_precos.update');
    Route::delete('produtos/imagens', [ProdutosController::class, 'excluirTodasImagens'])
        ->where(['empresa' => '[0-9]+'])
        ->name('produtos.imagens.destroy_all');

    Route::post('produtos/configuracoes/categorias', [ProdutosController::class, 'storeCategoria'])
        ->where(['empresa' => '[0-9]+'])
        ->name('produtos.config.categorias.store');
    Route::put('produtos/configuracoes/categorias/{categoria}', [ProdutosController::class, 'updateCategoria'])
        ->where(['empresa' => '[0-9]+', 'categoria' => '[0-9]+'])
        ->name('produtos.config.categorias.update');
    Route::delete('produtos/configuracoes/categorias/{categoria}', [ProdutosController::class, 'destroyCategoria'])
        ->where(['empresa' => '[0-9]+', 'categoria' => '[0-9]+'])
        ->name('produtos.config.categorias.destroy');

    Route::post('produtos/configuracoes/variacoes', [ProdutosController::class, 'storeVariacao'])
        ->where(['empresa' => '[0-9]+'])
        ->name('produtos.config.variacoes.store');
    Route::put('produtos/configuracoes/variacoes/{variacao}', [ProdutosController::class, 'updateVariacao'])
        ->where(['empresa' => '[0-9]+', 'variacao' => '[0-9]+'])
        ->name('produtos.config.variacoes.update');
    Route::delete('produtos/configuracoes/variacoes/{variacao}', [ProdutosController::class, 'destroyVariacao'])
        ->where(['empresa' => '[0-9]+', 'variacao' => '[0-9]+'])
        ->name('produtos.config.variacoes.destroy');

    Route::post('produtos/configuracoes/inatividade', [ProdutosController::class, 'salvarInatividade'])
        ->where(['empresa' => '[0-9]+'])
        ->name('produtos.config.inatividade.save');

    Route::post('produtos/configuracoes/tributacoes', [ProdutosController::class, 'storeTributacao'])
        ->where(['empresa' => '[0-9]+'])
        ->name('produtos.config.tributacoes.store');
    Route::put('produtos/configuracoes/tributacoes/{tributacao}', [ProdutosController::class, 'updateTributacao'])
        ->where(['empresa' => '[0-9]+', 'tributacao' => '[0-9]+'])
        ->name('produtos.config.tributacoes.update');
    Route::delete('produtos/configuracoes/tributacoes/{tributacao}', [ProdutosController::class, 'destroyTributacao'])
        ->where(['empresa' => '[0-9]+', 'tributacao' => '[0-9]+'])
        ->name('produtos.config.tributacoes.destroy');

    Route::post('produtos/promocoes', [ProdutosController::class, 'storePromocao'])
        ->where(['empresa' => '[0-9]+'])
        ->name('produtos.promocoes.store');
    Route::delete('produtos/promocoes/{promocao}', [ProdutosController::class, 'destroyPromocao'])
        ->where(['empresa' => '[0-9]+', 'promocao' => '[0-9]+'])
        ->name('produtos.promocoes.destroy');

    Route::post('produtos/destaques', [ProdutosController::class, 'storeDestaque'])
        ->where(['empresa' => '[0-9]+'])
        ->name('produtos.destaques.store');
    Route::put('produtos/destaques/{destaque}', [ProdutosController::class, 'updateDestaque'])
        ->where(['empresa' => '[0-9]+', 'destaque' => '[0-9]+'])
        ->name('produtos.destaques.update');
    Route::delete('produtos/destaques/{destaque}', [ProdutosController::class, 'destroyDestaque'])
        ->where(['empresa' => '[0-9]+', 'destaque' => '[0-9]+'])
        ->name('produtos.destaques.destroy');
    Route::post('produtos/destaques/{destaque}/itens', [ProdutosController::class, 'addProdutoDestaque'])
        ->where(['empresa' => '[0-9]+', 'destaque' => '[0-9]+'])
        ->name('produtos.destaques.itens.store');
    Route::delete('produtos/destaques/{destaque}/itens/{item}', [ProdutosController::class, 'removeProdutoDestaque'])
        ->where(['empresa' => '[0-9]+', 'destaque' => '[0-9]+', 'item' => '[0-9]+'])
        ->name('produtos.destaques.itens.destroy');

    Route::resource('produtos', ProdutosController::class)->where(['empresa' => '[0-9]+']);
    Route::resource('dashboard', DashboardController::class)->where(['empresa' => '[0-9]+']);
});
