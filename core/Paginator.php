<?php
// ============================================================
// core/Paginator.php  — Pagination helper
// ============================================================

class Paginator
{
    private int $totalItems;
    private int $perPage;
    private int $currentPage;
    private int $totalPages;

    public function __construct(int $totalItems, int $perPage, int $currentPage)
    {
        $this->totalItems  = $totalItems;
        $this->perPage     = max(1, $perPage);
        $this->totalPages  = (int) ceil($totalItems / $this->perPage);
        $this->currentPage = max(1, min($currentPage, max(1, $this->totalPages)));
    }

    /** SQL OFFSET value for current page */
    public function offset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }

    /** Total number of pages */
    public function totalPages(): int
    {
        return $this->totalPages;
    }

    /** Whether a previous page exists */
    public function hasPrev(): bool
    {
        return $this->currentPage > 1;
    }

    /** Whether a next page exists */
    public function hasNext(): bool
    {
        return $this->currentPage < $this->totalPages;
    }

    public function currentPage(): int
    {
        return $this->currentPage;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function totalItems(): int
    {
        return $this->totalItems;
    }

    /** First item number on this page (for "Showing X–Y of Z") */
    public function firstItem(): int
    {
        return $this->totalItems === 0 ? 0 : $this->offset() + 1;
    }

    /** Last item number on this page */
    public function lastItem(): int
    {
        return min($this->offset() + $this->perPage, $this->totalItems);
    }

    /**
     * Returns an array of page numbers to display.
     * Shows up to 5 pages centered around current page.
     */
    public function pages(): array
    {
        $start = max(1, $this->currentPage - 2);
        $end   = min($this->totalPages, $start + 4);
        $start = max(1, $end - 4);

        return range($start, $end);
    }
}
