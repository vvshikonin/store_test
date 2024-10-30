import { shallowMount } from '@vue/test-utils';
import ExpenseSummary from '@/pages/expense_summary.vue';
import { expenseSummaryAPI, expenseTypeAPI } from '@/api/expense_summary_api';

jest.mock('@/api/expense_summary_api');

describe('ExpenseSummary.vue', () => {
    let wrapper;

    beforeEach(() => {
        expenseSummaryAPI.generate.mockResolvedValue({});
        expenseSummaryAPI.index.mockResolvedValue({ data: { data: [] } });
        expenseTypeAPI.index.mockResolvedValue({ data: { data: [] } });

        wrapper = shallowMount(ExpenseSummary);
    });

    it('должен монтироваться корректно', () => {
        expect(wrapper.exists()).toBe(true);
    });

    it('должен вызывать generateSummaries при монтировании', async () => {
        await wrapper.vm.$nextTick();
        expect(expenseSummaryAPI.generate).toHaveBeenCalled();
    });

    it('должен вызывать loadExpenseTypes при монтировании', async () => {
        await wrapper.vm.$nextTick();
        expect(expenseTypeAPI.index).toHaveBeenCalled();
    });

    it('должен вызывать loadSummaries при монтировании', async () => {
        await wrapper.vm.$nextTick();
        expect(expenseSummaryAPI.index).toHaveBeenCalled();
    });

    it('должен корректно форматировать дату', () => {
        const date = '2023-10-01';
        const formattedDate = wrapper.vm.formatDate(date);
        expect(formattedDate).toBe('01.10.2023');
    });

    // Добавь больше тестов для других методов и функционала
});