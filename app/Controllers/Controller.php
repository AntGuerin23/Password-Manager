<?php namespace Controllers;

use Exception;
use Models\Brokers\ConnectionBroker;
use Models\Brokers\PasswordBroker;
use Models\ConnectionUpdater;
use Models\Encryption;
use Models\SessionHelper;
use Zephyrus\Application\Flash;
use Zephyrus\Application\Session;
use Zephyrus\Network\Response;

/**
 * This class acts as an application middleware, all other controller classes should extends this Controller and thus
 * inherit every global behaviors your application may require. You can override methods like before() and after() to
 * make good use of the middleware feature, or simply override method like render() to define specific variables that
 * all views should have. You can have as much middleware as you want (through extends).
 *
 * Class Controller
 * @package Controllers
 */
abstract class Controller extends SecurityController
{
    /**
     * Override example of the render method to automatically include arguments to be sent to any views for any
     * Controller class extending this middleware. Useful for global data used in layout files.
     *
     * @param string $page
     * @param array $args
     * @return Response
     */
    public function render($page, $args = []): Response
    {
        return parent::render($page, array_merge($args, [
            'system_date' => date(FORMAT_DATE_TIME)
        ]));
    }

    /**
     * This method is called immediately before processing any route in your controller. To break the chain of
     * middleware, you can remove the call to parent::before() method, but it is highly discouraged. Instead, you should
     * always keep the parent call, but place it accordingly to your situation (should the parent's middleware
     * processing be done before or after mine?).
     *
     * If this method returns a Response, the whole execution chain is broken and the Response is directly returned. It
     * is useful for some security validations before any route processing. Should be removed if not used.
     *
     * @return Response | null
     */
    public function before(): ?Response
    {

        if ($this->checkForDisconnection() || $this->checkIfEncryptionFails()) {
            return $this->redirect("/login");
        }
        return parent::before();
    }

    /**
     * This method is called after processing any route in your controller. It receives the processed response as
     * argument which you can modify and then return too to another middleware or the client response. Should be removed
     * if not used.
     *
     * @param Response $response
     * @return Response | null
     */
    public function after(?Response $response): ?Response
    {
        return parent::after($response);
    }

    private function checkForDisconnection(): bool
    {
        ConnectionUpdater::update();
        $broker = new ConnectionBroker();
        $connection = $broker->findBySessionId();
        if ($connection != null && $broker->isDisconnected($connection->id)) {
            Session::getInstance()->destroy();
            $broker->delete($connection->id);
            Flash::warning("You have been disconnected");
            return true;
        }
        return false;
    }

    private function checkIfEncryptionFails(): bool
    {
        if (!str_contains($this->request->getRequestedUri(), "api") && !str_contains($this->request->getRequestedUri(), "login") && !str_contains($this->request->getRequestedUri(), "register")) {
            $broker = new PasswordBroker();
            try {
                $passwords = $broker->findAllForUser(SessionHelper::getUserId());
                foreach ($passwords as $password) {
                    $cipher = Encryption::encryptPassword($password->password);
                    Encryption::decryptPassword($cipher);
                    break;
                }
            } catch (Exception) {
                ConnectionUpdater::disconnect();
                setcookie("userKey", "", 1);
                Session::getInstance()->restart();
                Flash::warning("An error has occurred, please reconnect.");
                return true;
            }
        }
        return false;
    }
}
