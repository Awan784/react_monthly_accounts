// calculationEngine.ts

export type MonthlyTotal = {
  month: string;
  year: number;
  platformId?: string;
  accountId?: string;
  income?: number;
  gmv?: number;
  videos?: number;
  itemsSold?: number;
};

export type DailyEntry = {
  date?: string;
  month: string;
  year: number;
  platformId?: string;
  accountId?: string;
  income?: number;
  gmv?: number;
  videos?: number;
  itemsSold?: number;
};

export type BrandDeal = {
  month: string;
  year: number;
  platformId?: string;
  accountId?: string;
  amount?: number;
  status: "Paid" | "Pending" | "Overdue" | "Follow-Up";
};

export type Expense = {
  month: string;
  year: number;
  platformId?: string | null;
  amount?: number;
};

export type ExternalIncome = {
  month: string;
  year: number;
  amount?: number;
};

export type Filters = {
  year?: number;
  month?: string | "All";
  platformId?: string | "All";
  accountId?: string | "All";
};

export type CalculationInput = {
  monthlyTotals: MonthlyTotal[];
  dailyEntries: DailyEntry[];
  brandDeals: BrandDeal[];
  expenses: Expense[];
  externalIncome: ExternalIncome[];
  taxRate: number;
  filters?: Filters;
};

function n(value: unknown): number {
  const parsed = Number(value ?? 0);
  return Number.isFinite(parsed) ? parsed : 0;
}

function matchesFilter(row: any, filters?: Filters): boolean {
  if (!filters) return true;
  if (filters.year && Number(row.year) !== Number(filters.year)) return false;
  if (filters.month && filters.month !== "All" && row.month !== filters.month) return false;
  if (filters.platformId && filters.platformId !== "All" && row.platformId !== filters.platformId) return false;
  if (filters.accountId && filters.accountId !== "All" && row.accountId !== filters.accountId) return false;
  return true;
}

export function calculateDashboardTotals(input: CalculationInput) {
  const filters = input.filters;

  const monthlyTotals = input.monthlyTotals.filter(row => matchesFilter(row, filters));
  const dailyEntries = input.dailyEntries.filter(row => matchesFilter(row, filters));
  const brandDeals = input.brandDeals.filter(row => matchesFilter(row, filters));
  const expenses = input.expenses.filter(row => matchesFilter(row, filters));
  const externalIncome = input.externalIncome.filter(row => matchesFilter(row, filters));

  const monthlyIncome = monthlyTotals.reduce((sum, row) => sum + n(row.income), 0);
  const dailyIncome = dailyEntries.reduce((sum, row) => sum + n(row.income), 0);
  const platformIncome = monthlyIncome + dailyIncome;

  const monthlyGmv = monthlyTotals.reduce((sum, row) => sum + n(row.gmv), 0);
  const dailyGmv = dailyEntries.reduce((sum, row) => sum + n(row.gmv), 0);
  const gmv = monthlyGmv + dailyGmv;

  const monthlyVideos = monthlyTotals.reduce((sum, row) => sum + n(row.videos), 0);
  const dailyVideos = dailyEntries.reduce((sum, row) => sum + n(row.videos), 0);
  const videos = monthlyVideos + dailyVideos;

  const monthlyItems = monthlyTotals.reduce((sum, row) => sum + n(row.itemsSold), 0);
  const dailyItems = dailyEntries.reduce((sum, row) => sum + n(row.itemsSold), 0);
  const itemsSold = monthlyItems + dailyItems;

  const paidBrandDeals = brandDeals
    .filter(row => row.status === "Paid")
    .reduce((sum, row) => sum + n(row.amount), 0);

  const paymentsOwed = brandDeals
    .filter(row => row.status !== "Paid")
    .reduce((sum, row) => sum + n(row.amount), 0);

  const external = externalIncome.reduce((sum, row) => sum + n(row.amount), 0);
  const expenseTotal = expenses.reduce((sum, row) => sum + n(row.amount), 0);

  const totalIncome = platformIncome + paidBrandDeals + external;
  const estimatedTaxes = totalIncome * (n(input.taxRate) / 100);
  const net = totalIncome - estimatedTaxes - expenseTotal;

  return {
    monthlyIncome,
    dailyIncome,
    platformIncome,
    paidBrandDeals,
    paymentsOwed,
    externalIncome: external,
    expenses: expenseTotal,
    totalIncome,
    estimatedTaxes,
    net,
    gmv,
    videos,
    itemsSold
  };
}
