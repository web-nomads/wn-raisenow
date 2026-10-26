<?php

declare(strict_types=1);

namespace WebNomads\WnRaisenow\Domain\Repository;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Reads donation forms and their purposes for the frontend, with enable
 * fields respected and the current language overlaid.
 */
class DonationFormRepository
{
    public const TABLE_FORM = 'tx_wnraisenow_form';
    public const TABLE_PURPOSE = 'tx_wnraisenow_purpose';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function findVisibleByUid(int $uid): ?array
    {
        if ($uid <= 0) {
            return null;
        }
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_FORM);
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        $row = $queryBuilder
            ->select('*')
            ->from(self::TABLE_FORM)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchAssociative();
        if (!is_array($row)) {
            return null;
        }
        // relation may point to a translation, overlay always starts from the default language
        $parent = (int)($row['l10n_parent'] ?? 0);
        if ((int)($row['sys_language_uid'] ?? 0) > 0 && $parent > 0) {
            return $this->findVisibleByUid($parent);
        }

        return $this->overlay(self::TABLE_FORM, $row);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findPurposesOfForm(int $formUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_PURPOSE);
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        $rows = $queryBuilder
            ->select('*')
            ->from(self::TABLE_PURPOSE)
            ->where(
                $queryBuilder->expr()->eq('form', $queryBuilder->createNamedParameter($formUid, Connection::PARAM_INT)),
                $queryBuilder->expr()->in('sys_language_uid', $queryBuilder->createNamedParameter([0, -1], Connection::PARAM_INT_ARRAY))
            )
            ->orderBy('sorting')
            ->executeQuery()
            ->fetchAllAssociative();

        $purposes = [];
        foreach ($rows as $row) {
            $overlaid = $this->overlay(self::TABLE_PURPOSE, $row);
            if ($overlaid !== null) {
                $purposes[] = $overlaid;
            }
        }

        return $purposes;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>|null
     */
    private function overlay(string $table, array $row): ?array
    {
        $overlaid = GeneralUtility::makeInstance(PageRepository::class)->getLanguageOverlay($table, $row);

        return is_array($overlaid) ? $overlaid : null;
    }
}
