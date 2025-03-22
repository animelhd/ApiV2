<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendCodeRestorePassword extends Mailable
{
    use Queueable, SerializesModels;

    public string $codigo;

    /**
     * Create a new message instance.
     *
     * @param string $codigo
     */
    public function __construct(string $codigo)
    {
        $this->codigo = $codigo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->view('mail.codeRestorePassword')->subject("Restablecimiento de contraseña");
    }
}