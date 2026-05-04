// jsonImporter.ts

type BackupJson = {
  daily?: any[];
  monthlyAdjustments?: any[];
  brands?: any[];
  expenses?: any[];
  external?: any[];
  settings?: any;
  auditLog?: any[];
};

export function mapBackupJsonToAppData(backup: BackupJson) {
  return {
    monthlyTotals: (backup.monthlyAdjustments || []).map(row => ({
      legacyId: row.id,
      month: row.month,
      year: Number(row.year),
      platformName: row.platform,
      accountName: row.account,
      income: Number(row.amount || 0),
      gmv: Number(row.gmv || 0),
      videos: Number(row.videos || 0),
      itemsSold: Number(row.items || 0),
      notes: row.notes || ""
    })),

    dailyEntries: (backup.daily || []).map(row => ({
      legacyId: row.id,
      date: row.date || null,
      month: row.month,
      year: Number(row.year),
      platformName: row.platform,
      accountName: row.account,
      income: Number(row.earnings || row.income || 0),
      gmv: Number(row.gmv || 0),
      videos: Number(row.videos || 0),
      itemsSold: Number(row.items || row.itemsSold || 0),
      notes: row.notes || ""
    })),

    brandDeals: (backup.brands || []).map(row => ({
      legacyId: row.id,
      date: row.date || null,
      month: row.month,
      year: Number(row.year),
      platformName: row.platform,
      accountName: row.account,
      brand: row.brand || "",
      contact: row.contact || "",
      product: row.product || "",
      amount: Number(row.amount || 0),
      status: row.status || "Pending",
      dueDate: row.due || null,
      usageRights: row.usage || "",
      notes: row.notes || "",
      contract: row.contract || null
    })),

    expenses: (backup.expenses || []).map(row => ({
      legacyId: row.id,
      date: row.date || null,
      month: row.month,
      year: Number(row.year),
      category: row.category || "",
      platformName: row.platform || "General",
      amount: Number(row.amount || 0),
      notes: row.notes || "",
      receipt: row.receipt || null
    })),

    externalIncome: (backup.external || []).map(row => ({
      legacyId: row.id,
      date: row.date || null,
      month: row.month,
      year: Number(row.year),
      source: row.source || "",
      amount: Number(row.amount || 0),
      notes: row.notes || ""
    })),

    settings: {
      taxRate: Number(backup.settings?.taxRate ?? 22),
      incomeGoal: Number(backup.settings?.incomeGoal ?? 250000),
      monthlyGoal: Number(backup.settings?.monthlyGoal ?? 20000),
      accounts: backup.settings?.accounts || ["DynamiteFinds", "DynamiteFindsMore", "Trisha"],
      platforms: backup.settings?.platforms || ["TikTok Shop", "Amazon", "Facebook", "YouTube", "Instagram"],
      expenseCategories: backup.settings?.expenseCategories || [],
      usageRights: backup.settings?.usageRights || []
    },

    auditLog: backup.auditLog || []
  };
}
