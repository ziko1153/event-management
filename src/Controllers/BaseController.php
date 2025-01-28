<?php

namespace App\Controllers;

class BaseController
{
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    protected function sendValidationError(array $errors, ?array $oldData = null): void
    {
        $isAjax = $this->isAjaxRequest();

        if ($isAjax) {
            $this->jsonResponse([
                'success' => false,
                'error' => is_array($errors) ? reset($errors) : $errors,
                'errors' => is_array($errors) ? $errors : [$errors],
                'old' => $oldData
            ], 422);
        }

        $_SESSION['error'] = is_array($errors) ? reset($errors) : $errors;
        $_SESSION['errors'] = $errors;
        if ($oldData) {
            $_SESSION['old'] = $oldData;
        }

        $this->redirect($this->getReferer());
    }

    protected function sendSuccess(string $message, ?string $redirectUrl = null): void
    {
        if ($this->isAjaxRequest()) {
            $this->jsonResponse([
                'success' => true,
                'message' => $message,
                'redirect' => $redirectUrl
            ]);
        }

        $_SESSION['success'] = $message;
        $this->redirect($redirectUrl ?? $this->getReferer());
    }

    protected function sendError(string $message, ?string $redirectUrl = null): void
    {
        if ($this->isAjaxRequest()) {
            $this->jsonResponse([
                'success' => false,
                'message' => $message,
                'redirect' => $redirectUrl
            ], 500);
        }

        $_SESSION['error'] = $message;
        $this->redirect($redirectUrl ?? $this->getReferer());
    }

    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    protected function isAjaxRequest(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function getReferer(): string
    {
        return $_SERVER['HTTP_REFERER'] ?? '/';
    }
}
