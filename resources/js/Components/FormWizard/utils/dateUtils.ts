/**
 * Date utility functions for form handling
 */

/**
 * Calculate loan start date (1st of next month)
 */
export const calculateLoanStartDate = (): string => {
  try {
    // Set to 1st of the next month
    const today = new Date();
    return new Date(today.getFullYear(), today.getMonth() + 1, 1).toISOString().split('T')[0];
  } catch (error) {
    console.error("Error calculating loan start date:", error);
    return new Date().toISOString().split('T')[0];
  }
};

/**
 * Calculate loan end date based on start date and loan period
 */
export const calculateLoanEndDate = (startDateStr: string, loanPeriodMonths: number): string => {
  try {
    const startDate = new Date(startDateStr);
    // Create a date for the loan period months later
    // Then get the last day of that month
    // Note: To get the last day of a month after adding loanPeriodMonths,
    // we need to get day 0 of the month after 
    const endDate = new Date(startDate.getFullYear(), startDate.getMonth() + loanPeriodMonths + 1, 0);
    return endDate.toISOString().split('T')[0];
  } catch (error) {
    console.error("Error calculating loan end date:", error);
    // Fallback: create a date 3 months from now and get the last day of that month
    const today = new Date();
    const fallbackEndDate = new Date(today.getFullYear(), today.getMonth() + 4, 0);
    return fallbackEndDate.toISOString().split('T')[0];
  }
};

/**
 * Format date for display (e.g., 01 Jan 2025)
 */
export const formatDateForDisplay = (dateStr: string): string => {
  try {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'});
  } catch (error) {
    console.error("Error formatting date:", error);
    return dateStr;
  }
};

/**
 * Add months to a date
 */
export const addMonths = (date: Date, months: number): Date => {
  const result = new Date(date);
  result.setMonth(result.getMonth() + months);
  return result;
};

/**
 * Get the last day of a month
 */
export const getLastDayOfMonth = (year: number, month: number): number => {
  return new Date(year, month + 1, 0).getDate();
};

/**
 * Get the number of days between two dates
 */
export const getDaysBetween = (startDate: Date, endDate: Date): number => {
  const oneDay = 24 * 60 * 60 * 1000; // hours*minutes*seconds*milliseconds
  return Math.round(Math.abs((startDate.getTime() - endDate.getTime()) / oneDay));
};

/**
 * Zimbabwe provinces for address selection
 */
export const zimbabweProvinces = [
  'Bulawayo',
  'Harare',
  'Manicaland',
  'Mashonaland Central',
  'Mashonaland East',
  'Mashonaland West',
  'Masvingo',
  'Matabeleland North',
  'Matabeleland South',
  'Midlands'
];

/**
 * Zimbabwe districts by province for address selection
 */
export const zimbabweDistricts: Record<string, string[]> = {
  'Manicaland': ['Buhera', 'Chimanimani', 'Chipinge', 'Makoni', 'Mutare', 'Mutasa', 'Nyanga'],
  'Mashonaland Central': ['Bindura', 'Centenary', 'Guruve', 'Mazowe', 'Mount Darwin', 'Rushinga', 'Shamva'],
  'Mashonaland East': ['Chikomba', 'Goromonzi', 'Hwedza', 'Marondera', 'Mudzi', 'Murehwa', 'Mutoko', 'Seke', 'UMP'],
  'Mashonaland West': ['Chegutu', 'Chinhoyi', 'Hurungwe', 'Kadoma', 'Kariba', 'Makonde', 'Mhondoro-Ngezi', 'Sanyati', 'Zvimba'],
  'Masvingo': ['Bikita', 'Chiredzi', 'Chivi', 'Gutu', 'Masvingo', 'Mwenezi', 'Zaka'],
  'Matabeleland North': ['Binga', 'Bubi', 'Hwange', 'Lupane', 'Nkayi', 'Tsholotsho', 'Umguza'],
  'Matabeleland South': ['Beitbridge', 'Bulilima', 'Gwanda', 'Insiza', 'Mangwe', 'Matobo', 'Umzingwane'],
  'Midlands': ['Chirumhanzu', 'Gokwe North', 'Gokwe South', 'Gweru', 'Kwekwe', 'Mberengwa', 'Shurugwi', 'Zvishavane']
};