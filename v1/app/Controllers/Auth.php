<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
    public function login(): string|ResponseInterface
    {
        if ((bool) session('auth_logged_in')) {
            return redirect()->to('/');
        }

        return view('auth/login', ['title' => 'Login']);
    }

    public function autenticar()
    {
        $idcard = trim((string) $this->request->getPost('idcard'));
        $senha = (string) $this->request->getPost('senha');

        if (! preg_match('/^\d{8}$/', $idcard)) {
            return redirect()->back()->with('erro', 'Informe um ID do cracha com 8 digitos.')->withInput();
        }

        $db = db_connect();

        if (! $db->tableExists('usuarios')) {
            return redirect()->back()->with('erro', 'Tabela de usuarios nao encontrada. Execute as migrations.');
        }

        $usuario = $db->table('usuarios')->where('idcard', $idcard)->get()->getRowArray();

        if ($usuario === null) {
            return redirect()->back()->with('erro', 'Usuario nao encontrado para o cracha informado.')->withInput();
        }

        if ((int) ($usuario['primeiro_acesso'] ?? 1) === 1) {
            session()->set('primeiro_acesso_user_id', (int) $usuario['id']);

            return redirect()->to('/login/primeiro-acesso');
        }

        if ($senha === '') {
            return redirect()->back()->with('erro', 'Informe sua senha.')->withInput();
        }

        $senhaBanco = (string) ($usuario['senha'] ?? '');
        $senhaValida = password_verify($senha, $senhaBanco) || hash_equals($senhaBanco, $senha);

        if (! $senhaValida) {
            return redirect()->back()->with('erro', 'Senha invalida.')->withInput();
        }

        if (! password_get_info($senhaBanco)['algo']) {
            $db->table('usuarios')->where('id', (int) $usuario['id'])->update([
                'senha' => password_hash($senha, PASSWORD_DEFAULT),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->setSessaoAutenticada($usuario);

        return redirect()->to('/');
    }

    public function primeiroAcesso(): string|ResponseInterface
    {
        if (session('primeiro_acesso_user_id') === null) {
            return redirect()->to('/login');
        }

        return view('auth/primeiro_acesso', ['title' => 'Primeiro Acesso']);
    }

    public function salvarPrimeiroAcesso()
    {
        $userId = (int) (session('primeiro_acesso_user_id') ?? 0);

        if ($userId <= 0) {
            return redirect()->to('/login');
        }

        $email = trim((string) $this->request->getPost('email'));
        $senha = (string) $this->request->getPost('senha_nova');
        $confirmacao = (string) $this->request->getPost('senha_confirmacao');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('erro', 'Informe um e-mail valido.')->withInput();
        }

        if (strlen($senha) < 6) {
            return redirect()->back()->with('erro', 'A senha deve ter no minimo 6 caracteres.')->withInput();
        }

        if ($senha !== $confirmacao) {
            return redirect()->back()->with('erro', 'A confirmacao da senha nao confere.')->withInput();
        }

        $db = db_connect();

        $usuario = $db->table('usuarios')->where('id', $userId)->get()->getRowArray();

        if ($usuario === null) {
            session()->remove('primeiro_acesso_user_id');

            return redirect()->to('/login')->with('erro', 'Usuario nao encontrado.');
        }

        $db->table('usuarios')->where('id', $userId)->update([
            'email' => $email,
            'senha' => password_hash($senha, PASSWORD_DEFAULT),
            'primeiro_acesso' => 0,
            'data_primeira_entrada' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $usuario['email'] = $email;
        $usuario['primeiro_acesso'] = 0;

        session()->remove('primeiro_acesso_user_id');
        $this->setSessaoAutenticada($usuario);

        return redirect()->to('/')->with('sucesso', 'Primeiro acesso concluido com sucesso.');
    }

    public function logout()
    {
        session()->remove([
            'auth_logged_in',
            'auth_user_id',
            'auth_nome',
            'auth_is_admin',
        ]);

        return redirect()->to('/login')->with('sucesso', 'Voce saiu da sessao.');
    }

    /**
     * @param array<string, mixed> $usuario
     */
    private function setSessaoAutenticada(array $usuario): void
    {
        session()->set([
            'auth_logged_in' => true,
            'auth_user_id' => (int) ($usuario['id'] ?? 0),
            'auth_nome' => (string) ($usuario['nome_completo'] ?? $usuario['usuario'] ?? 'Usuario'),
            'auth_is_admin' => (int) ($usuario['is_admin'] ?? 0) === 1,
        ]);
    }
}