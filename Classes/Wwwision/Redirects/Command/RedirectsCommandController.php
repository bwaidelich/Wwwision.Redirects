<?php
namespace Wwwision\Redirects\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Wwwision.Redirects".    *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Wwwision\Redirects\Domain\Model\Redirection;
use Wwwision\Redirects\Domain\Service\RedirectService;

/**
 * @Flow\Scope("singleton")
 */
class RedirectsCommandController extends \TYPO3\Flow\Cli\CommandController
{

    /**
     * @Flow\Inject
     * @var RedirectService
     */
    protected $redirectService;

    /**
     * List all redirections
     *
     * @return void
     */
    public function listCommand()
    {
        $redirections = $this->redirectService->getAll();
        $numberOfRedirections = count($redirections);
        if ($numberOfRedirections === 0) {
            $this->outputLine('There are no redirections configured');
            $this->quit();
        }
        $this->outputLine('There are %d redirections configured:', [$numberOfRedirections]);
        $rows = [];
        /** @var Redirection $redirection */
        foreach ($redirections as $redirection) {
            $rows[] = [$redirection->getRequestPattern(), $redirection->getTarget(), $redirection->getStatusCode()];
        }
        $this->output->outputTable($rows, ['source', 'target', 'status code']);
    }

    /**
     * Add a new redirection
     *
     * @param string $requestPattern Request pattern for which this redirect should be triggered
     * @param string $target Target URL to which a redirect should be pointed
     * @param integer $statusCode Status code to be send with the redirect header (defaults to 301)
     */
    public function addCommand($requestPattern, $target, $statusCode = 301)
    {
        $this->redirectService->add(new Redirection($requestPattern, $target, $statusCode));
        $this->outputLine('Redirection "%s" => "%s" (%d) was added!', [$requestPattern, $target, $statusCode]);
    }


    /**
     * Remove one redirection
     *
     * @param string $requestPattern
     * @return void
     */
    public function removeCommand($requestPattern)
    {
        try {
            $this->redirectService->remove($requestPattern);
        } catch (\Exception $exception) {
            $this->outputLine($exception->getMessage());
            $this->quit(1);
        }
        $this->outputLine('The redirection "%s" was removed!', [$requestPattern]);
    }

    /**
     * Remove all redirections
     *
     * @param boolean $force safety belt
     * @return void
     */
    public function removeAllCommand($force = false)
    {
        if (!$force) {
            $this->output->outputLine('Add --force if you really want to delete all redirections!');
            $this->quit(1);
        }
        $this->redirectService->removeAll();
        $this->outputLine('All redirections were removed.');
    }

}