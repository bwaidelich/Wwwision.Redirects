<?php
namespace Wwwision\Redirects\Domain\Service;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Exception;
use TYPO3\Flow\Http\Request as HttpRequest;
use TYPO3\Flow\Mvc\Routing\RouterCachingService;
use TYPO3\Flow\Persistence\QueryResultInterface;
use Wwwision\Redirects\Domain\Model\Redirection;
use Wwwision\Redirects\Domain\Repository\RedirectionRepository;
use Wwwision\Redirects\RedirectionException;

/**
 * Central authority for HTTP redirects.
 * This service is used to redirect to any configured target URI *after* the Routing Framework kicks in and it
 * should be used to create new redirection instances.
 * @Flow\Scope("singleton")
 */
class RedirectService
{

    /**
     * @Flow\Inject
     * @var RedirectionRepository
     */
    protected $redirectionRepository;

    /**
     * Make exit() a closure so it can be manipulated during tests
     *
     * @var \Closure
     */
    protected $exit;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->exit = function () { exit; };
    }

    /**
     * Searches for an applicable redirection record in the $redirectionRepository and sends redirect headers if one was found
     *
     * @param HttpRequest $request
     * @return void
     * @api
     */
    public function triggerRedirectIfApplicable(HttpRequest $request)
    {
        try {
            $redirection = $this->redirectionRepository->findOneByRequest($request);
        } catch (\Exception $exception) {
            // skip triggering the redirect if there was an error accessing the database (wrong credentials, ...)
            return;
        }
        if ($redirection === NULL) {
            return;
        }
        $this->sendRedirectHeaders($request, $redirection);
        $this->exit->__invoke();
    }

    /**
     * @param HttpRequest $request
     * @param Redirection $redirection
     * @return void
     */
    protected function sendRedirectHeaders(HttpRequest $request, Redirection $redirection)
    {
        if (headers_sent()) {
            return;
        }
        $sourceUriPath = $redirection->getRequestPattern();
        $targetUriPath = $redirection->getTarget();
        if (strpos($sourceUriPath, '%') !== false) {
            $sourceUriPath = preg_quote($sourceUriPath, '/');
            $sourceUriPath = str_replace('%', '(.*)', $sourceUriPath);
            $targetUriPath = preg_replace('/' . $sourceUriPath . '/', $redirection->getTarget(), $request->getRelativePath());
        }

        header($redirection->getStatusLine());
        header('Location: ' . $request->getBaseUri() . $targetUriPath);
    }

    /**
     * Returns all registered redirection records
     *
     * @return Redirection[]
     */
    public function getAll()
    {
        return $this->redirectionRepository->findAll()->toArray();
    }

    /**
     * Adds a redirection to the repository and updates related redirection instances accordingly
     *
     * @param Redirection $redirection
     * @return void
     */
    public function add(Redirection $redirection)
    {
        $this->updateDependingRedirects($redirection);
        $this->redirectionRepository->add($redirection);
    }

    /**
     * Removes the redirection with the given request pattern
     *
     * @param string $requestPattern
     * @return void
     * @throws \InvalidArgumentException
     */
    public function remove($requestPattern)
    {
        $redirection = $this->redirectionRepository->findByIdentifier($requestPattern);
        if ($redirection === null) {
            throw new \InvalidArgumentException(sprintf('Redirection "%s" could not be found!', $requestPattern), 1453909209);
        }
        $this->redirectionRepository->remove($redirection);
    }


    /**
     * Removes all registered redirection records
     *
     * @return void
     */
    public function removeAll()
    {
        $this->redirectionRepository->removeAll();
    }

    /**
     * Updates affected redirection instances in order to avoid redundant or circular redirects
     *
     * @param Redirection $newRedirection
     * @return void
     */
    protected function updateDependingRedirects(Redirection $newRedirection)
    {
        // TODO
//        /** @var $existingRedirectionForSourceUriPath Redirection */
//        $existingRedirectionForSourceUriPath = $this->redirectionRepository->findOneBySourceUriPath($newRedirection->getRequestPattern());
//        /** @var $existingRedirectionForTargetUriPath Redirection */
//        $existingRedirectionForTargetUriPath = $this->redirectionRepository->findOneBySourceUriPath($newRedirection->getTarget());
//
//        if ($existingRedirectionForTargetUriPath !== NULL) {
//            if ($existingRedirectionForTargetUriPath->getTarget() === $newRedirection->getRequestPattern()) {
//                $this->redirectionRepository->remove($existingRedirectionForTargetUriPath);
//            } else {
//                throw new RedirectionException(sprintf('A redirect exists for the target URI path "%s", please remove it first.', $newRedirection->getTarget()), 1382091526);
//            }
//        }
//        if ($existingRedirectionForSourceUriPath !== NULL) {
//            throw new RedirectionException(sprintf('A redirect exists for the source URI path "%s", please remove it first.', $newRedirection->getRequestPattern()), 1382091456);
//        }
//        $obsoleteRedirectionInstances = $this->redirectionRepository->findByTargetUriPath($newRedirection->getRequestPattern());
//        /** @var $obsoleteRedirection Redirection */
//        foreach ($obsoleteRedirectionInstances as $obsoleteRedirection) {
//            if ($obsoleteRedirection->getRequestPattern() === $newRedirection->getTarget()) {
//                $this->redirectionRepository->remove($obsoleteRedirection);
//            } else {
//                $obsoleteRedirection->setTargetUriPath($newRedirection->getTarget());
//                $this->redirectionRepository->update($obsoleteRedirection);
//            }
//        }
    }

}