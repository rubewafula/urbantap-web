<?php


namespace App\Traits;


use App\Utilities\RabbitMQ;
use App\Utilities\Utils;
use Illuminate\Support\Facades\Log;

/**
 * Trait SendEmailTrait
 * @package App\Traits
 */
trait SendEmailTrait
{
    /**
     * @var string
     */
    private $path = "/app/public/static/mailer/";

    /**
     * Send email.
     * All data should be provided in the data array
     *
     * @param array $data
     * @param string $template
     */
    public function send(array $data, string $template)
    {
        $mailContents = file_get_contents(storage_path(sprintf('%s%s', $this->path, $template)));
        if ($data['to']) {
            (new RabbitMQ())->publish(
                array_merge(
                    $data,
                    [
                        'email' => Utils::loadTemplateData($mailContents, $data)
                    ]
                ),
                env('EMAIL_MESSAGE_QUEUE'), env('EMAIL_MESSAGE_EXCHANGE'), env('EMAIL_MESSAGE_ROUTE')
            );
        } else {
            Log::info("Email info missing, skipped notification");
        }
    }
}