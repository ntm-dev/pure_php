<?php

namespace Core\Support\Mailer;

use Core\View;
use PHPMailer\PHPMailer\PHPMailer;
use Core\Support\Facades\RedisService;
use Repositories\Book\AdminRepository;


/**
 * Define rule for validation.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
/**
 * Mail helper.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class Mail
{
    const DEFAULT_CHARSET = "UTF-8";
    const DEFAULT_FROM_NAME = "Repitte";
    const DEFAULT_REPLY_NAME = "Information";
    const DEFAULT_LANGUAGE = [
        'code' => 'en',
        'path' => '/libraries/PHPMailer/language/phpmailer.lang-ja.php'
    ];

    /** @var PHPMailer */
    private $mailer;

    /** @var View */
    private $view;

    /** @var AdminRepository */
    private $adminRepository;

    public function __construct()
    {
        $this->view = new View;
        $this->mailer = new PHPMailer;
        // $this->adminRepository = new AdminRepository;
        $this->setDefaultConfig();
    }

    /**
     * Get config from database.
     *
     * @return array|false
     */
    private function getConfig()
    {
        return $this->adminRepository->getConfigMail();
    }

    /**
     * Set default config
     *
     * @return void
     */
    private function setDefaultConfig()
    {
        $mailConfig = $this->getConfig();
        $this->setConfig(...(array_values($mailConfig)));
        $this->mailer->isSMTP();
        $this->mailer->isHTML(true);
        $this->mailer->CharSet = static::DEFAULT_CHARSET;
        $this->mailer->SMTPAuth = true;
        $this->from($mailConfig['mail_address']);
        $this->subject(static::DEFAULT_FROM_NAME);
        $this->replyTo($mailConfig['mail_address'], static::DEFAULT_REPLY_NAME);
        $this->mailer->setLanguage(static::DEFAULT_LANGUAGE['code'], static::DEFAULT_LANGUAGE['path']);
    }

    /**
     * Set mailer config
     *
     * @param  string  $host
     * @param  string  $userName
     * @param  string  $passWord
     * @param  string  $SMTPSecure
     * @param  string  $port
     */
    public function setConfig($host, $userName, $passWord, $SMTPSecure, $port)
    {
        $this->mailer->Host = $host;
        $this->mailer->Port = $port;
        $this->mailer->Username = $userName;
        $this->mailer->Password = $passWord;
        $this->mailer->SMTPSecure = $SMTPSecure;

        return $this;
    }

    /**
     * Render body and send it.
     *
     * @return bool
     */
    public function send()
    {
        try {
            $this->body($this->getMailBody());

            return $this->mailer->send();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Render body and send it to queue.
     *
     * @return bool
     */
    public function sendToQueue()
    {
        // return RedisService::add(
        //     getenv('REDIS_LINE_MAIL_DB_NAME'),
        //     [
        //         'title'         => $this->mailer->Subject,
        //         'list_to'       => array_keys($this->mailer->getAllRecipientAddresses()),
        //         'mail_body'     => $this->getMailBody(),
        //         'emailFrom'     => $this->mailer->From,
        //         'emailFromName' => $this->mailer->FromName,
        //     ]
        // );
    }

    /**
     * Get mail body
     *
     * @return  string
     */
    private function getMailBody()
    {
        if ($this->mailer->Body) {
            return $this->mailer->Body;
        }

        return $this->view->render();
    }

    /**
     * Pass data to template.
     *
     * @param  array $data
     * @return $this
     */
    public function with(array $data)
    {
        $this->view->assign($data);

        return $this;
    }

    /** 
     * Set mail body
     *
     * @param  string  $body
     * @return $this
     */
    public function body($body)
    {
        $this->mailer->Body = $body;

        return $this;
    }

    private function addReceiver($kind, $address, $name = '')
    {
        $method = "add{$kind}";
        if (is_array($address)) {
            foreach ($address as $value) {
                if (!empty($value)) {
                    $this->mailer->$method($value, $name);
                }
            }
        } else {
            if (empty($address)) {
                throw new \LogicException("Mail address can not be empty");
            }
            $this->mailer->$method($address, $name);
        }

        return $this;
    }

    /**
     * Add a "To" address.
     *
     * @param string $address The email address to send to
     * @param string $name
     *
     * @throws Exception
     *
     * @return $this
     */
    public function to($address, $name = '')
    {
        return $this->addReceiver("Address", $address, $name);
    }

    /**
     * Add a "CC" address.
     *
     * @param array|string $address The email address to send to
     * @param string $name
     *
     * @throws Exception
     *
     * @return $this
     */
    public function cc($address, $name = '')
    {
        return $this->addReceiver("CC", $address, $name);
    }

    
    /**
     * Add a "BCC" address.
     *
     * @param array|string $address The email address to send to
     * @param string $name
     *
     * @throws Exception
     *
     * @return $this
     */
    public function bcc($address, $name = '')
    {
        return $this->addReceiver("BCC", $address, $name);
    }


    /**
     * Set the From and FromName properties.
     *
     * @param string $address
     * @param string $name
     * @param bool   $auto    Whether to also set the Sender address, defaults to true
     *
     * @throws Exception
     *
     * @return $this
     */
    public function from($address, $name = '', $auto = true)
    {
        $this->mailer->setFrom($address, $name ?: static::DEFAULT_FROM_NAME, $auto);

        return $this;
    }

    /**
     * Set the Subject of the email.
     *
     * @param  string  $value
     * @return $this
     */
    public function subject($value)
    {
        $this->mailer->Subject = $value;

        return $this;
    }

    /**
     * Set view template for html email
     *
     * @param  string  $path
     * @return $this
     */
    public function view($path)
    {
        $this->mailer->isHTML(true);

        $this->view->setTempate($path);

        return $this;
    }

    /**
     * Set view template for plain/text email
     *
     * @param  string  $path
     * @return $this
     */
    public function text($path)
    {
        $this->mailer->isHTML(false);

        $this->view->setTempate($path);

        return $this;
    }

    /**
     * Add a "Reply-To" address.
     *
     * @param string $address The email address to reply to
     * @param string $name
     *
     * @throws Exception
     *
     * @return $this;
     */
    public function replyTo($address, $name = '')
    {
        $this->mailer->addReplyTo($address, $name);

        return $this;
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this->mailer, $method) && is_callable([$this->mailer, $method])) {
            return $this->mailer->$method(...$arguments);
        }

        throw new \BadMethodCallException("Method [$method] does not exist or is not accessible");
    }
}
