import React from 'react';
import { ChevronRight } from 'lucide-react';
import StepContainer from '../../common/StepContainer';
import Button from '../../common/Button';
import { ProductSelectionProps, Product, CreditOption } from '../types';

const ProductSelection: React.FC<ProductSelectionProps> = ({
  onBack,
  categories,
  currentCategory,
  categoryHistory,
  selectedProductId,
  onCategoryClick,
  onBackClick,
  onProductSelect,
  loading,
  error
}) => {
  const handleCreditOptionSelection = (product: Product, option: CreditOption) => {
    onProductSelect(product, option);
  };

  return (
    <StepContainer
      title={currentCategory ? currentCategory.name : 'Select a Category'}
      subtitle="Browse our available products and credit options"
      showBackButton
      onBack={onBack}
    >
      {loading ? (
        <div className="p-6 md:p-8 text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-500 mx-auto"></div>
          <p className="mt-4 text-gray-600">Loading...</p>
        </div>
      ) : error ? (
        <div className="p-6 md:p-8 text-center">
          <p className="text-red-500">{error}</p>
          <button
            onClick={() => currentCategory ? onCategoryClick(currentCategory) : onBackClick()}
            className="mt-4 px-6 py-2 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600"
          >
            Retry
          </button>
        </div>
      ) : (
        <div className="p-6 md:p-8 space-y-6">
          {categoryHistory.length > 0 && (
            <div className="flex flex-wrap items-center gap-2 text-sm text-gray-600">
              <button
                onClick={onBackClick}
                className="text-emerald-600 hover:text-emerald-700"
              >
                Back
              </button>
              {categoryHistory.map((cat, index) => (
                <React.Fragment key={cat.id}>
                  <ChevronRight className="w-4 h-4" />
                  <span>{cat.name}</span>
                </React.Fragment>
              ))}
            </div>
          )}

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {categories.length > 0 ? (
              categories.map((category) => (
                <Button
                  key={category.id}
                  onClick={() => onCategoryClick(category)}
                  icon={ChevronRight}
                  fullWidth
                >
                  {category.name}
                </Button>
              ))
            ) : currentCategory?.products?.length ? (
              currentCategory.products.map((product) => (
                <div
                  key={product.id}
                  className="bg-white rounded-xl overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-lg"
                >
                  <img
                    src={product.image}
                    alt={product.name}
                    className="w-full h-48 object-cover"
                  />
                  <div className="p-4 space-y-4">
                    <h3 className="font-semibold text-lg">{product.name}</h3>

                    {selectedProductId === product.id ? (
                      <div className="space-y-3">
                        <h4 className="font-medium text-gray-700">Select Credit Options:</h4>
                        <div className="grid grid-cols-1 gap-2">
                          {product.credit_options.map((option) => (
                            <button
                              key={option.months}
                              onClick={() => handleCreditOptionSelection(product, option)}
                              className="p-3 rounded-xl border transition-all hover:border-emerald-500 hover:bg-emerald-50"
                            >
                              <div className="flex justify-between items-center">
                                <div>
                                  <div className="font-medium">{option.months} Months</div>
                                </div>
                                <div className="text-emerald-600 font-semibold">
                                  ${option.installment_amount}/month
                                </div>
                              </div>
                            </button>
                          ))}
                        </div>
                      </div>
                    ) : (
                      <Button
                        onClick={() => onProductSelect(product, product.credit_options[0])}
                        variant="primary"
                        fullWidth
                      >
                        Select Instalment/Period
                      </Button>
                    )}
                  </div>
                </div>
              ))
            ) : (
              <div className="col-span-full text-center py-12 text-gray-500">
                No items found in this category
              </div>
            )}
          </div>
        </div>
      )}
    </StepContainer>
  );
};

export default ProductSelection;