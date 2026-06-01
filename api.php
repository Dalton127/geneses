<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        listarProdutos();
        break;
    case 'save':
        salvarProduto();
        break;
    case 'delete':
        excluirProduto();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Ação inválida. Use action=list|save|delete']);
        break;
}

function listarProdutos() {
    $mysqli = getConnection();
    $resultado = $mysqli->query("SELECT id, codigo, nome, categoria, preco, quantidade, atualizado_em FROM produtos ORDER BY id DESC");
    if (!$resultado) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar produtos: ' . $mysqli->error]);
        return;
    }

    $produtos = [];
    while ($row = $resultado->fetch_assoc()) {
        $row['preco'] = (float) $row['preco'];
        $row['quantidade'] = (int) $row['quantidade'];
        $produtos[] = $row;
    }

    echo json_encode($produtos);
}

function salvarProduto() {
    $input = json_decode(file_get_contents('php://input'), true);
    $body = $input ?: $_POST;

    $id = $body['id'] ?? null;
    $codigo = trim($body['codigo'] ?? '');
    $nome = trim($body['nome'] ?? '');
    $categoria = trim($body['categoria'] ?? '');
    $preco = isset($body['preco']) ? floatval($body['preco']) : null;
    $quantidade = isset($body['quantidade']) ? intval($body['quantidade']) : null;

    if ($codigo === '' || $nome === '' || $categoria === '' || $preco === null || $quantidade === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Campos obrigatórios não preenchidos.']);
        return;
    }

    $mysqli = getConnection();
    $codigo = $mysqli->real_escape_string($codigo);
    $nome = $mysqli->real_escape_string($nome);
    $categoria = $mysqli->real_escape_string($categoria);

    if ($id) {
        $id = intval($id);
        $sql = "UPDATE produtos SET codigo = '$codigo', nome = '$nome', categoria = '$categoria', preco = $preco, quantidade = $quantidade, atualizado_em = NOW() WHERE id = $id";
    } else {
        $sql = "INSERT INTO produtos (codigo, nome, categoria, preco, quantidade, atualizado_em) VALUES ('$codigo', '$nome', '$categoria', $preco, $quantidade, NOW())";
    }

    if (!$mysqli->query($sql)) {
        http_response_code(500);
        if ($mysqli->errno === 1062) {
            echo json_encode(['error' => 'Já existe um produto com esse código SKU.']);
        } else {
            echo json_encode(['error' => 'Erro ao salvar produto: ' . $mysqli->error]);
        }
        return;
    }

    echo json_encode(['success' => true]);
}

function excluirProduto() {
    $input = json_decode(file_get_contents('php://input'), true);
    $body = $input ?: $_POST;

    $id = isset($body['id']) ? intval($body['id']) : null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID do produto não informado.']);
        return;
    }

    $mysqli = getConnection();
    $sql = "DELETE FROM produtos WHERE id = $id";

    if (!$mysqli->query($sql)) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao excluir produto: ' . $mysqli->error]);
        return;
    }

    echo json_encode(['success' => true]);
}
