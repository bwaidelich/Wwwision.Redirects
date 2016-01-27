<?php
namespace Wwwision\Redirects\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Wwwision.Redirects".    *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Http\Request as HttpRequest;
use TYPO3\Flow\Persistence\Doctrine\Repository;
use TYPO3\Flow\Persistence\QueryInterface;
use Wwwision\Redirects\Domain\Model\Redirection;

/**
 * Repository for Redirection records - This is for internal use only, refer to the RedirectService!
 *
 * @Flow\Scope("singleton")
 */
class RedirectionRepository extends Repository
{

    /**
     * @var array
     */
    protected $defaultOrderings = ['requestPattern' => QueryInterface::ORDER_ASCENDING];

    /**
     * @param HttpRequest $request
     * @return Redirection
     */
    public function findOneByRequest(HttpRequest $request)
    {
        $requestPath = preg_replace('/(\\\\|%|_)/', '\\\\$1', $request->getRelativePath());
        $query = $this->createQuery();
        $query->getQueryBuilder()->where(':requestPath LIKE e.requestPattern')->setParameter('requestPath', $requestPath);
        return $query
            ->execute()
            ->getFirst();
    }
}