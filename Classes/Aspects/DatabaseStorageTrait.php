<?php

namespace WebExcess\RedirectHandler\Backend\Aspects;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Flow\Persistence\QueryResultInterface;

trait DatabaseStorageTrait
{

    /**
     * @param string $sourceUriPath
     * @param string $targetUriPath
     * @param string $statusCode
     * @param string $host
     * @return QueryResultInterface
     */
    public function search($sourceUriPath = null, $targetUriPath = null, $statusCode = null, $host = null)
    {

        $searchParameters = [];
        if ($sourceUriPath) {
            $searchParameters['sourceUriPath'] = '%' . trim($sourceUriPath, '/') . '%';
        }
        if ($targetUriPath) {
            $searchParameters['targetUriPath'] = '%' . trim($targetUriPath, '/') . '%';
        }
        if ($statusCode) {
            $searchParameters['statusCode'] = $statusCode . '%';
        }

        $searchSqlProperties = array_map(function ($name) {
            return sprintf('r.%s LIKE :%s', $name, $name);
        }, array_keys($searchParameters));

        $sql = 'SELECT r FROM Neos\RedirectHandler\DatabaseStorage\Domain\Model\Redirect r';
        if ($searchParameters || $host) {
            $sql .= ' WHERE';
        }
        if ($searchSqlProperties) {
            $sql .= ' ' . implode(' AND ', $searchSqlProperties);
        }
        if ($host) {
            if ($searchParameters) {
                $sql .= ' AND (r.host LIKE :host OR r.host IS NULL)';
            } else {
                $sql .= ' (r.host LIKE :host)';
            }
        }
        $sql .= ' ORDER BY r.host ASC, r.sourceUriPath ASC';

        /** @var QueryInterface $query */
        $query = $this->entityManager->createQuery($sql);
        $query->setParameters($searchParameters);
        if ($host) {
            $query->setParameter('host', '%' . $host . '%');
        }

        return $query->execute();
    }

}
