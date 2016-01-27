<?php
namespace Wwwision\Redirects\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Http\Response as HttpResponse;

/**
 * A Redirection model that represents a HTTP redirect
 *
 * @see RedirectService
 *
 * @Flow\Entity
 */
class Redirection
{

    /**
     * Request pattern for which this redirect should be triggered
     * Usually that's just the relative request path, but it might contain placeholders like "%"
     *
     * @var string
     * @ORM\Id
     */
    protected $requestPattern;

    /**
     * Target URL to which a redirect should be pointed
     * Usually that's just the relative URI path
     *
     * @var string
     */
    protected $target;

    /**
     * Status code to be send with the redirect header
     *
     * @var integer
     * @Flow\Validate(type="NumberRange", options={ "minimum"=100, "maximum"=599 })
     */
    protected $statusCode;

    /**
     * @param string $requestPattern Request pattern for which this redirect should be triggered
     * @param string $target Target URL to which a redirect should be pointed
     * @param integer $statusCode status code to be send with the redirect header
     */
    public function __construct($requestPattern, $target, $statusCode = 301)
    {
        $this->requestPattern = $requestPattern;
        $this->target = $target;
        $this->statusCode = $statusCode;
    }

    /**
     * @return string
     */
    public function getRequestPattern()
    {
        return $this->requestPattern;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getStatusLine()
    {
        return sprintf('HTTP/1.1 %d %s', $this->statusCode, HttpResponse::getStatusMessageByCode($this->statusCode));
    }

}