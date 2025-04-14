/**
 * Date utilities for loan applications
 */

/**
 * Calculates the loan start and end dates based on a given loan period (in months)
 * @param months Number of months for the loan
 * @returns Object containing start and end dates in ISO format
 */
export const calculateLoanDates = (months: number): { startDate: string; endDate: string } => {
  // Get the first day of next month for the start date
  const today = new Date();
  const startDate = new Date(today.getFullYear(), today.getMonth() + 1, 1);

  // Calculate end date based on loan period (months)
  // To get the last day of a month: create a date for the first day of the next month, then subtract one day
  const endDate = new Date(startDate.getFullYear(), startDate.getMonth() + months + 1, 0);

  return {
    startDate: startDate.toISOString().slice(0, 10), // YYYY-MM-DD format
    endDate: endDate.toISOString().slice(0, 10)
  };
};

/**
 * Formats a date for display in the Zimbabwe format
 * @param dateString ISO date string
 * @returns Formatted date string (e.g., "01 Jan 2025")
 */
export const formatDateText = (dateString: string): string => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

/**
 * Calculates the payment date based on a start date and offset days
 * @param startDate ISO date string
 * @param offsetDays Number of days to offset from start date
 * @returns ISO format date string for the payment date
 */
export const calculatePaymentDate = (startDate: string, offsetDays: number = 0): string => {
  const date = new Date(startDate);
  date.setDate(date.getDate() + offsetDays);
  return date.toISOString().slice(0, 10);
};

/**
 * Gets the start of a month
 * @param date Date to get the start of the month for
 * @returns Date object for the first day of the month
 */
export const getStartOfMonth = (date: Date = new Date()): Date => {
  return new Date(date.getFullYear(), date.getMonth(), 1);
};

/**
 * Gets the end of a month
 * @param date Date to get the end of the month for
 * @returns Date object for the last day of the month
 */
export const getEndOfMonth = (date: Date = new Date()): Date => {
  return new Date(date.getFullYear(), date.getMonth() + 1, 0);
};

/**
 * Adds months to a date
 * @param date Base date
 * @param months Number of months to add
 * @returns New date with added months
 */
export const addMonths = (date: Date, months: number): Date => {
  const result = new Date(date);
  result.setMonth(result.getMonth() + months);
  return result;
};

/**
 * Calculate the number of months between two dates
 */
export const monthsBetween = (startDate: Date, endDate: Date): number => {
  return (
    endDate.getMonth() - 
    startDate.getMonth() + 
    (12 * (endDate.getFullYear() - startDate.getFullYear()))
  );
};