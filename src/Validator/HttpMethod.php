<?php
/**
 * @link  https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\Validator;

use Zend\Validator\AbstractValidator;
use OldTown\Workflow\ZF2\Dispatch\Dispatcher\WorkflowDispatchEventInterface;
use Zend\Http\Request;

/**
 * Class HttpMethod
 *
 * @package OldTown\Workflow\ZF2\Toolkit\Validator
 */
class HttpMethod extends AbstractValidator
{
    /**
     * @var string
     */
    const HTTP_METHOD_INVALID        = 'httpMethodInvalid';

    /**
     * @var string
     */
    const VALIDATE_VALUE_INVALID        = 'validateValueInvalid';

    /**
     * @var string
     */
    const REQUEST_INVALID        = 'requestInvalid';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = [
        self::HTTP_METHOD_INVALID      => 'Http method invalid',
        self::VALIDATE_VALUE_INVALID      => 'Validate value not implement WorkflowDispatchEventInterface',
        self::REQUEST_INVALID      => 'The request is not http',
    ];

    /**
     * Настройки валидатора
     *
     * @var array|null
     */
    protected $allowedHttpMethods;


    /**
     * @param WorkflowDispatchEventInterface $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        if (!$value instanceof WorkflowDispatchEventInterface) {
            $this->error(self::VALIDATE_VALUE_INVALID);
            return false;
        }

        if (null === $this->getAllowedHttpMethods()) {
            return true;
        }

        $request = $value->getMvcEvent()->getRequest();
        if (!$request instanceof Request) {
            $this->error(self::REQUEST_INVALID);
            return false;
        }

        /** @var Request  $request*/
        $method = $request->getMethod();
        $method = strtolower($method);

        $allowedHttpMethods = $this->getAllowedHttpMethods();
        if (!array_key_exists($method, $allowedHttpMethods)) {
            $this->error(self::HTTP_METHOD_INVALID);
            return false;
        }



        return true;
    }

    /**
     * @return array
     */
    public function getAllowedHttpMethods()
    {
        return $this->allowedHttpMethods;
    }

    /**
     * @param array|null $allowedHttpMethods
     *
     * @return $this
     */
    public function setAllowedHttpMethods(array $allowedHttpMethods = null)
    {
        $results = null;
        if (is_array($allowedHttpMethods)) {
            $results = array_map(function ($method) {
                $method = trim($method);
                $method = strtolower($method);

                return $method;
            }, $allowedHttpMethods);

            $results = array_combine($results, $results);
        }
        $this->allowedHttpMethods = $results;

        return $this;
    }
}
