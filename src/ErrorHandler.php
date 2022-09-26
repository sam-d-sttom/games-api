<?php

//class to handle all Errors
class ErrorHandler
{
    //function to handle all throwable exception.
    public static function handleException(Throwable $exception): void
    {
        http_response_code(500);

        echo json_encode([
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine()
        ]);
    }

    public static function handleError(int $errSeverity, string $errMsg,  string $errFile, int $errLine): bool
    {
        throw new ErrorException($errMsg, 0, $errSeverity, $errFile, $errLine);
    }

   
}