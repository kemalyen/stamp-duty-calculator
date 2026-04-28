<?php

use App\Services\StampDutyCalculator;
use Livewire\Component;

new class extends Component
{
    public float|int $propertyPrice;
    public string $propertyType = 'residential';
    public bool $firstTimeBuyer = false;
    public bool $additionalProperty = false;

    public ?array $result = [];
    public ?string $error = null;

    protected array $rules = [
        'propertyPrice' => 'required|numeric|min:0',
        'propertyType' => 'required|in:residential',
    ];

    public function calculate(StampDutyCalculator $calculator)
    {
        $this->validate();

        $price = (float) $this->propertyPrice;

        // Additional property is determined by checkbox
        $isAdditional = $this->additionalProperty;

        // First-time buyer relief only applies to residential
        $isFirstTime = $this->propertyType === 'residential' && $this->firstTimeBuyer;

        $this->result = $calculator->calculate($price, $isFirstTime, $isAdditional);
        $this->error = null;
    }

    public function updated($property)
    {
        // Clear results when inputs change
        if ($this->result !== null) {
            $this->result = null;
        }
    }
}
?>

<x-slot name="title">
    Calculator
</x-slot>

<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Stamp Duty Calculator</h2>
            <p class="text-gray-600 mb-8">Calculate stamp duty on property purchases</p>

            <form wire:submit="calculate" class="space-y-6">
                <!-- Property Price -->
                <div>
                    <label for="property_price" class="block text-sm font-medium text-gray-700 mb-2">
                        Property Price (£)
                    </label>
                    <input
                        type="number"
                        id="property_price"
                        wire:model="propertyPrice"
                        step="0.01"
                        min="0"
                        placeholder="Enter property price"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition" />
                    @error('propertyPrice') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Property Type -->
                <div>
                    <label for="property_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Property Type
                    </label>
                    <select
                        id="property_type"
                        wire:model="propertyType"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition">
                        <option value="">Select property type</option>
                        <option value="residential">Residential</option>
                    </select>
                    @error('propertyType') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- First Time Buyer -->
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="first_time_buyer"
                        wire:model="firstTimeBuyer"
                        class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 cursor-pointer" />
                    <label for="first_time_buyer" class="ml-2 text-sm font-medium text-gray-700 cursor-pointer">
                        First Time Buyer (Relief applies)
                    </label>
                </div>

                <!-- Additional Property -->
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="additional_property"
                        wire:model="additionalProperty"
                        class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 cursor-pointer" />
                    <label for="additional_property" class="ml-2 text-sm font-medium text-gray-700 cursor-pointer">
                        Additional Property (3% surcharge applies)
                    </label>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Calculate Stamp Duty
                </button>
            </form>

            <!-- Results Section -->
            @if ($result)
            <div class="mt-8 pt-8 border-t-2 border-gray-200">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Calculation Results</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600">Property Price</p>
                        <p class="text-2xl font-bold text-gray-900">£{{ number_format($propertyPrice, 2) }}</p>
                    </div>
                    <div class="bg-indigo-50 p-4 rounded-lg">
                        <p class="text-sm text-indigo-600 font-medium">Stamp Duty</p>
                        <p class="text-2xl font-bold text-indigo-600">£{{ number_format($result['total'], 2) }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600">Effective Rate</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $result['effective_rate'] }}%</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p class="text-sm text-green-600 font-medium">Total Amount Due</p>
                        <p class="text-2xl font-bold text-green-600">£{{ number_format((float)$propertyPrice + $result['total'], 2) }}</p>
                    </div>
                </div>

                <!-- Breakdown -->
                @if(count($result['breakdown']) > 0)
                <div class="mt-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-3">How this is calculated</h4>
                    <div class="space-y-3">
                        @foreach($result['breakdown'] as $band)
                        <div class="bg-gray-50 p-4 rounded-lg flex justify-between items-center">
                            <div>
                                <p class="text-sm font-medium text-gray-700">
                                    @if(isset($band['label']))
                                    {{ $band['label'] }}
                                    @else
                                    £{{ number_format($band['from']) }} - £{{ number_format($band['to']) }}
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500">{{ $band['rate'] }}% rate</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">£{{ number_format($band['taxable_amount'], 2) }} at {{ $band['rate'] }}%</p>
                                <p class="text-lg font-bold text-gray-900">£{{ number_format($band['tax'], 2) }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>