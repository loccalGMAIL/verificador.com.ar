@extends('layouts.app')

@section('title', 'Etiquetas')
@section('page-title', 'Etiquetas y Códigos de Barras')

@section('content')

<div x-data="labelManager()" x-init="init()">

    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,280px)_260px_280px] gap-4 items-start">

        {{-- ════════════════════════════════════════════════
             COLUMNA 1 — Lista de productos (compacta)
        ════════════════════════════════════════════════ --}}
        <div>

            {{-- Barra de búsqueda + filtro --}}
            <div class="flex flex-col gap-1.5 mb-2">
                <input type="text" x-model="search" placeholder="Buscar..."
                       class="border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs
                              focus:outline-none focus:ring-1 focus:ring-blue-500">
                <select x-model="filter"
                        class="border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs
                               focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="all">Todos</option>
                    <option value="without">Sin código</option>
                    <option value="with">Con código</option>
                </select>
            </div>

            {{-- Acciones de selección --}}
            <div class="flex items-center justify-between mb-1.5 px-0.5">
                <p class="text-xs text-slate-500">
                    <span x-text="filteredProducts.length"></span> prod.
                    <template x-if="selected.length > 0">
                        <span>
                            · <strong x-text="selectedProductsWithBarcode.length"></strong> sel.
                            · <strong x-text="selectedLabels.length"></strong> etiq.
                        </span>
                    </template>
                </p>
                <div class="flex gap-1">
                    <button @click="selectAllFiltered()" class="text-xs text-blue-600 hover:underline">Todos</button>
                    <button @click="selected = []" class="text-xs text-slate-500 hover:underline">X</button>
                </div>
            </div>

            {{-- Lista de productos --}}
            <div class="bg-white rounded-lg border border-slate-200 divide-y divide-slate-100 text-xs max-h-[600px] overflow-y-auto">

                <template x-if="filteredProducts.length === 0">
                    <div class="px-2.5 py-6 text-center">
                        <i class="fa-solid fa-barcode text-2xl text-slate-300 mb-1 block"></i>
                        <p class="text-slate-500 text-xs">Sin productos.</p>
                    </div>
                </template>

                <template x-for="product in pagedProducts" :key="product.id">
                    <div class="flex items-start gap-1.5 px-2 py-1.5 hover:bg-slate-50 transition">

                        {{-- Checkbox --}}
                        <input type="checkbox"
                               :checked="isSelected(product.id)"
                               :disabled="!product.barcode"
                               @change="toggleSelect(product.id)"
                               class="mt-0.5 w-3.5 h-3.5 text-blue-600 rounded border-slate-300 cursor-pointer
                                      disabled:opacity-30 disabled:cursor-not-allowed shrink-0">

                        {{-- Info del producto --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-slate-800 truncate" x-text="product.name"></p>

                            {{-- Tiene barcode --}}
                            <template x-if="product.barcode">
                                <span class="inline-flex items-center gap-0.5 mt-0.5 text-xs font-mono text-emerald-700 bg-emerald-50 px-1 py-0.5 rounded-full border border-emerald-200 truncate">
                                    <i class="fa-solid fa-barcode text-emerald-500" style="font-size:9px"></i>
                                    <span x-text="product.barcode" class="text-xs"></span>
                                </span>
                            </template>

                            {{-- Sin barcode: formulario inline --}}
                            <template x-if="!product.barcode">
                                <div class="mt-1 space-y-1">
                                    <div class="flex gap-0.5 items-center">
                                        <input type="text"
                                               :value="editing[product.id]?.value || ''"
                                               @input="onBarcodeInput(product.id, $event.target.value)"
                                               placeholder="Código"
                                               maxlength="50"
                                               class="border border-slate-300 rounded px-1 py-0.5 text-xs font-mono flex-1
                                                      focus:outline-none focus:ring-1 focus:ring-blue-500">

                                        <template x-if="editing[product.id]?.status === 'checking'">
                                            <i class="fa-solid fa-circle-notch fa-spin text-slate-400 text-xs"></i>
                                        </template>
                                        <template x-if="editing[product.id]?.status === 'ok'">
                                            <i class="fa-solid fa-circle-check text-emerald-500 text-xs" title="Disponible"></i>
                                        </template>
                                        <template x-if="editing[product.id]?.status === 'taken'">
                                            <i class="fa-solid fa-circle-xmark text-red-500 text-xs"
                                               :title="editing[product.id]?.message"></i>
                                        </template>
                                        <template x-if="editing[product.id]?.status === 'saving'">
                                            <i class="fa-solid fa-circle-notch fa-spin text-blue-500 text-xs"></i>
                                        </template>

                                        <button @click="generateBarcode(product.id)"
                                                :disabled="editing[product.id]?.status === 'loading' || editing[product.id]?.status === 'saving'"
                                                title="Generar"
                                                class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-1 py-0.5 rounded text-xs transition
                                                       disabled:opacity-50 disabled:cursor-not-allowed">
                                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                                        </button>

                                        <button @click="saveBarcode(product.id)"
                                                :disabled="!editing[product.id]?.value || editing[product.id]?.status === 'taken' || editing[product.id]?.status === 'saving' || editing[product.id]?.status === 'checking'"
                                                title="Guardar"
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-1 py-0.5 rounded text-xs transition
                                                       disabled:opacity-40 disabled:cursor-not-allowed">
                                            <i class="fa-solid fa-floppy-disk"></i>
                                        </button>
                                    </div>

                                    <template x-if="editing[product.id]?.status === 'taken'">
                                        <p class="text-xs text-red-600 mt-0.5" x-text="editing[product.id]?.message"></p>
                                    </template>
                                </div>
                            </template>
                        </div>

                    </div>
                </template>
            </div>

            {{-- Paginación --}}
            <template x-if="totalPages > 1">
                <div class="flex items-center justify-between mt-1.5 px-0.5 gap-1">
                    <p class="text-xs text-slate-500">
                        <span x-text="page"></span> / <span x-text="totalPages"></span>
                    </p>
                    <div class="flex gap-0.5">
                        <button @click="page = Math.max(1, page - 1)"
                                :disabled="page === 1"
                                class="px-1.5 py-0.5 text-xs border border-slate-300 rounded bg-white hover:bg-slate-50
                                       disabled:opacity-40 disabled:cursor-not-allowed transition">
                            ←
                        </button>
                        <template x-for="p in pageNumbers" :key="p">
                            <button @click="if (p !== '…') page = p"
                                    :disabled="p === '…'"
                                    :class="p === page
                                        ? 'bg-blue-600 text-white border-blue-600'
                                        : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'"
                                    class="px-1.5 py-0.5 text-xs border rounded transition disabled:cursor-default"
                                    x-text="p">
                            </button>
                        </template>
                        <button @click="page = Math.min(totalPages, page + 1)"
                                :disabled="page === totalPages"
                                class="px-1.5 py-0.5 text-xs border border-slate-300 rounded bg-white hover:bg-slate-50
                                       disabled:opacity-40 disabled:cursor-not-allowed transition">
                            →
                        </button>
                    </div>
                </div>
            </template>

            @if($products->isEmpty())
            <div class="mt-3 text-center">
                <p class="text-xs text-slate-500 mb-2">Sin productos.</p>
                <a href="{{ route('dashboard.products.create') }}"
                   class="inline-flex items-center gap-1 bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-blue-700 transition">
                    <i class="fa-solid fa-plus"></i> Crear
                </a>
            </div>
            @endif

        </div>

        {{-- ════════════════════════════════════════════════
             COLUMNA 2 — Características (compacta, acordeones)
        ════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-lg border border-slate-200 divide-y divide-slate-100 text-xs">

            {{-- Tipo de impresión --}}
            <div class="px-3 py-2.5">
                <p class="font-semibold text-slate-500 uppercase tracking-wide mb-2">Tipo</p>
                <div class="grid grid-cols-2 gap-1">
                    <button @click="printMode = 'a4'"
                            :class="printMode === 'a4' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'"
                            class="border rounded px-2 py-1 text-xs font-medium transition">
                        <i class="fa-solid fa-file"></i> A4
                    </button>
                    <button @click="printMode = 'label'"
                            :class="printMode === 'label' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'"
                            class="border rounded px-2 py-1 text-xs font-medium transition">
                        <i class="fa-solid fa-tag"></i> Etiqueta
                    </button>
                </div>
            </div>

            {{-- Configuración (acordeón) --}}
            <div>
                <button @click="openConfig = !openConfig"
                        class="w-full flex items-center justify-between px-3 py-2 text-left hover:bg-slate-50 transition">
                    <span class="font-semibold text-slate-500 uppercase tracking-wide"
                          x-text="printMode === 'a4' ? 'Diseño' : 'Tamaño'"></span>
                    <i class="fa-solid text-slate-400" :class="openConfig ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </button>
                <div x-show="openConfig" x-transition class="px-3 pb-2.5 space-y-2">
                    <template x-if="printMode === 'a4'">
                        <div>
                            <label class="text-slate-600 block mb-1">Columnas</label>
                            <div class="flex gap-0.5">
                                <template x-for="n in [1,2,3,4,5]" :key="n">
                                    <button @click="columns = n"
                                            :class="columns === n ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                            class="flex-1 py-0.5 rounded text-xs font-medium transition"
                                            x-text="n">
                                    </button>
                                </template>
                            </div>
                            <div class="grid grid-cols-2 gap-1.5 mt-2">
                                <div>
                                    <label class="text-slate-600 block mb-1">Margen (mm)</label>
                                    <input type="number" x-model.number="marginMm" min="0" max="20"
                                           class="w-full border border-slate-300 rounded px-1.5 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="text-slate-600 block mb-1">Espacio (mm)</label>
                                    <input type="number" x-model.number="spacingMm" min="0" max="10"
                                           class="w-full border border-slate-300 rounded px-1.5 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                                <div class="col-span-2">
                                    <label class="text-slate-600 block mb-1">
                                        Alto etiqueta (mm)
                                        <span class="text-slate-400">· <span x-text="pageCompositionCount"></span>/pág</span>
                                    </label>
                                    <input type="number" x-model.number="labelHeightMm" min="20" max="80"
                                           class="w-full border border-slate-300 rounded px-1.5 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>
                    </template>
                    <template x-if="printMode === 'label'">
                        <div>
                            <template x-for="opt in labelSizes" :key="opt.value">
                                <label class="flex items-center gap-1.5 cursor-pointer mb-1.5">
                                    <input type="radio" :value="opt.value" x-model="labelSize"
                                           class="text-blue-600 border-slate-300">
                                    <span class="text-slate-700 text-xs" x-text="opt.label"></span>
                                </label>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Personalización (acordeón) --}}
            <div>
                <button @click="openPersonalize = !openPersonalize"
                        class="w-full flex items-center justify-between px-3 py-2 text-left hover:bg-slate-50 transition">
                    <span class="font-semibold text-slate-500 uppercase tracking-wide">Personal.</span>
                    <i class="fa-solid text-slate-400" :class="openPersonalize ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </button>
                <div x-show="openPersonalize" x-transition class="px-3 pb-2.5 space-y-2">
                    <div>
                        <label class="text-slate-600 block mb-1">Nombre</label>
                        <div class="flex gap-0.5">
                            <template x-for="opt in fontSizes" :key="opt.value">
                                <button @click="nameFontSize = opt.value"
                                        :class="nameFontSize === opt.value ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="flex-1 py-0.5 rounded text-xs font-medium transition"
                                        x-text="opt.label">
                                </button>
                            </template>
                        </div>
                    </div>
                    <div>
                        <label class="text-slate-600 block mb-1">Código</label>
                        <div class="flex gap-0.5">
                            <template x-for="opt in barcodeHeights" :key="opt.value">
                                <button @click="barcodeHeight = opt.value"
                                        :class="barcodeHeight === opt.value ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="flex-1 py-0.5 rounded text-xs font-medium transition"
                                        x-text="opt.label">
                                </button>
                            </template>
                        </div>
                    </div>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" x-model="showBarcodeNumber"
                               class="w-3.5 h-3.5 text-blue-600 rounded border-slate-300">
                        <span class="text-slate-700">Mostrar número</span>
                    </label>

                    <div>
                        <label class="text-slate-600 block mb-1">Copias por producto</label>
                        <input type="number" x-model.number="copies" min="1" max="100"
                               class="w-full border border-slate-300 rounded px-1.5 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <p class="text-xs text-slate-400 mt-0.5">
                            Total: <span class="font-semibold" x-text="selectedLabels.length"></span> etiqueta(s)
                        </p>
                    </div>
                </div>
            </div>

            {{-- Botón imprimir --}}
            <div class="px-3 py-2.5">
                <button @click="submitPrint()"
                        :disabled="selectedProductsWithBarcode.length === 0"
                        class="w-full flex items-center justify-center gap-1 bg-blue-600 hover:bg-blue-700 text-white
                               font-semibold px-3 py-2 rounded text-xs transition disabled:opacity-40 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-print"></i>
                    Imprimir
                    <template x-if="selectedProductsWithBarcode.length > 0">
                        <span class="bg-blue-500 text-white text-xs font-bold rounded-full px-1.5 py-0.5"
                              x-text="selectedLabels.length"></span>
                    </template>
                </button>
                <template x-if="selected.length > 0 && selectedProductsWithBarcode.length < selected.length">
                    <p class="text-xs text-amber-600 mt-1 text-center">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span x-text="selected.length - selectedProductsWithBarcode.length"></span> sin código
                    </p>
                </template>
            </div>

        </div>

        {{-- ════════════════════════════════════════════════
             COLUMNA 3 — Vista previa (siempre visible)
        ════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-lg border border-slate-200 p-3 text-xs max-h-[600px] overflow-y-auto" x-ref="previewRoot">
            <p class="font-semibold text-slate-500 uppercase tracking-wide mb-2">Vista previa</p>

            <template x-if="selectedProductsWithBarcode.length > 0">
                <div>
                    {{-- Tabs A4 --}}
                    <template x-if="printMode === 'a4'">
                        <div class="flex gap-1 mb-2">
                            <button @click="previewTab = 'label'"
                                    :class="previewTab === 'label' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="flex-1 py-0.5 text-xs font-medium rounded transition">
                                Etiqueta
                            </button>
                            <button @click="previewTab = 'page'"
                                    :class="previewTab === 'page' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="flex-1 py-0.5 text-xs font-medium rounded transition">
                                Página
                            </button>
                        </div>
                    </template>

                    {{-- Preview etiqueta individual --}}
                    <template x-if="printMode !== 'a4' || previewTab === 'label'">
                        <div class="flex justify-center mb-2">
                            <div class="border border-slate-200 rounded-lg p-2 bg-slate-50 inline-flex flex-col items-center gap-1"
                                 :style="previewStyle()">
                                <p class="font-medium text-slate-800 leading-tight truncate w-full text-center text-xs"
                                   :style="'font-size:' + (fontSizeMap[nameFontSize] * 0.8) + 'px'"
                                   x-text="selectedLabels[0]?.name"></p>
                                <template x-if="showBarcodeNumber">
                                    <p class="font-mono text-slate-700 text-center text-xs" x-text="selectedLabels[0]?.barcode"></p>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Preview página A4 completa --}}
                    <template x-if="printMode === 'a4' && previewTab === 'page'">
                        <div class="border-2 border-slate-300 bg-white rounded overflow-hidden" style="aspect-ratio: 210/297">
                            <div :style="'width: 100%; height: 100%; display: grid; overflow: hidden; align-content: start; grid-template-columns: repeat(' + columns + ', 1fr); grid-auto-rows: ' + Math.max(10, labelHeightMm * 0.8) + 'px; gap: ' + (spacingMm * 0.8) + 'px; padding: ' + (marginMm * 0.8) + 'px'">
                                <template x-for="slot in pageCompositionCount" :key="slot">
                                    <div class="flex flex-col items-center justify-center border border-slate-200 rounded bg-white p-0.5 overflow-hidden text-xs" style="min-height: 0; min-width: 0; height: 100%">
                                        <template x-if="selectedLabels[slot - 1]">
                                            <div class="w-full h-full flex flex-col items-center justify-center">
                                                <p class="font-medium text-slate-800 text-center line-clamp-2 w-full leading-tight"
                                                   :style="'font-size: ' + Math.max(5, fontSizeMap[nameFontSize] * 0.5) + 'px'"
                                                   x-text="selectedLabels[slot - 1].name"></p>
                                                <template x-if="showBarcodeNumber">
                                                    <p class="font-mono text-slate-600 text-center w-full line-clamp-1 leading-tight"
                                                        :style="'font-size: ' + Math.max(4, 5 * 0.8) + 'px'"
                                                        x-text="selectedLabels[slot - 1].barcode"></p>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="!selectedLabels[slot - 1]">
                                            <div class="w-full h-full rounded bg-slate-50 border border-dashed border-slate-200"></div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 text-center mt-2">
                            <span x-text="columns"></span> col · <span x-text="pageCompositionCount"></span>/pág · <span x-text="Math.min(selectedLabels.length, pageCompositionCount)"></span>/<span x-text="selectedLabels.length"></span>
                        </p>
                    </template>

                    {{-- Preview etiqueta adhesiva --}}
                    <template x-if="printMode === 'label'">
                        <div class="text-center text-xs text-slate-500">
                            <p class="mb-1" x-text="labelSize"></p>
                            <div class="border border-slate-300 bg-white rounded mx-auto"
                                  :style="previewStyle() + '; margin-left: auto; margin-right: auto'">
                                <div class="flex flex-col items-center justify-center h-full p-1">
                                    <template x-if="showBarcodeNumber">
                                        <p class="font-mono text-slate-700 text-center text-xs" x-text="selectedLabels[0]?.barcode"></p>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="selectedProductsWithBarcode.length === 0">
                <div class="border border-dashed border-slate-300 rounded-lg p-2 text-center">
                    <i class="fa-solid fa-barcode text-lg text-slate-300 block mb-1"></i>
                    <p class="text-slate-400 text-xs">Seleccioná productos</p>
                </div>
            </template>
        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
function labelManager() {
    return {
        allProducts: @json($productsData),
        search: '',
        filter: 'all',
        selected: [],
        editing: {},
        checkTimers: {},
        page: 1,
        perPage: 6,

        printMode: 'a4',
        columns: 5,
        marginMm: 5,
        spacingMm: 3,
        labelHeightMm: 35,
        labelSize: '40x25',
        nameFontSize: 'md',
        barcodeHeight: 'md',
        showBarcodeNumber: true,
        copies: 1,

        openConfig: true,
        openPersonalize: true,
        openPreview: true,
        previewTab: 'label',

        labelSizes: [
            { value: '40x25', label: '40 × 25 mm  (pequeña)' },
            { value: '58x40', label: '58 × 40 mm  (mediana)' },
            { value: '62x30', label: '62 × 30 mm  (ancha)' },
        ],
        fontSizes:     [{ value: 'sm', label: 'Chico' }, { value: 'md', label: 'Medio' }, { value: 'lg', label: 'Grande' }],
        barcodeHeights:[{ value: 'sm', label: 'Bajo'  }, { value: 'md', label: 'Normal'}, { value: 'lg', label: 'Alto'   }],
        fontSizeMap:   { sm: 10, md: 13, lg: 16 },
        bcHeightMap:   { sm: 22, md: 34, lg: 48 },

        get filteredProducts() {
            const s = this.search.toLowerCase();
            return this.allProducts.filter(p => {
                const matchSearch = !s
                    || p.name.toLowerCase().includes(s)
                    || (p.barcode || '').includes(s);
                const matchFilter = this.filter === 'all'
                    || (this.filter === 'with'    &&  p.barcode)
                    || (this.filter === 'without' && !p.barcode);
                return matchSearch && matchFilter;
            });
        },

        get pagedProducts() {
            const start = (this.page - 1) * this.perPage;
            return this.filteredProducts.slice(start, start + this.perPage);
        },

        get totalPages() {
            return Math.max(1, Math.ceil(this.filteredProducts.length / this.perPage));
        },

        get pageNumbers() {
            const total = this.totalPages;
            if (total <= 7) { return Array.from({ length: total }, (_, i) => i + 1); }
            const cur = this.page;
            const pages = [1];
            if (cur > 3) { pages.push('…'); }
            for (let i = Math.max(2, cur - 1); i <= Math.min(total - 1, cur + 1); i++) { pages.push(i); }
            if (cur < total - 2) { pages.push('…'); }
            pages.push(total);
            return pages;
        },

        get selectedProductsWithBarcode() {
            return this.allProducts.filter(p => this.selected.includes(p.id) && p.barcode);
        },

        get selectedLabels() {
            const copies = Math.min(100, Math.max(1, parseInt(this.copies || 1, 10)));
            const products = this.selectedProductsWithBarcode;
            const labels = [];
            products.forEach(p => {
                for (let i = 0; i < copies; i++) {
                    labels.push(p);
                }
            });
            return labels;
        },

        get pageCompositionCount() {
            const usableH = 297 - 2 * this.marginMm;
            const labelH  = this.labelHeightMm || 35;
            const rows    = Math.max(1, Math.floor((usableH + this.spacingMm) / (labelH + this.spacingMm)));
            return this.columns * rows;
        },

        toggleSelect(id) {
            const i = this.selected.indexOf(id);
            if (i === -1) { this.selected.push(id); }
            else          { this.selected.splice(i, 1); }
        },

        isSelected(id) { return this.selected.includes(id); },

        selectAllFiltered() {
            const ids = this.filteredProducts
                .filter(p => p.barcode)
                .map(p => p.id);

            // Assign once to avoid triggering reactive work repeatedly.
            this.selected = Array.from(new Set([...this.selected, ...ids]));
        },

        ensureEditing(id) {
            if (!this.editing[id]) {
                this.editing[id] = { value: '', status: 'idle', message: '' };
            }
        },

        async generateBarcode(id) {
            this.ensureEditing(id);
            this.editing[id].status = 'loading';
            try {
                const r = await fetch('{{ route('dashboard.labels.generate') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                });
                const d = await r.json();
                this.editing[id].value  = d.barcode;
                this.editing[id].status = 'idle';
                this.checkBarcode(id);
            } catch (e) {
                this.editing[id].status = 'idle';
            }
        },

        onBarcodeInput(id, val) {
            this.ensureEditing(id);
            this.editing[id].value   = val;
            this.editing[id].status  = 'idle';
            this.editing[id].message = '';
            clearTimeout(this.checkTimers[id]);
            if (val.trim()) {
                this.checkTimers[id] = setTimeout(() => this.checkBarcode(id), 450);
            }
        },

        async checkBarcode(id) {
            const e = this.editing[id];
            if (!e || !e.value.trim()) { return; }
            e.status = 'checking';
            try {
                const r = await fetch('{{ route('dashboard.labels.check') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ barcode: e.value, product_id: id }),
                });
                const d = await r.json();
                e.status  = d.exists ? 'taken' : 'ok';
                e.message = d.exists ? ('En uso: ' + d.product_name) : 'Disponible';
            } catch (err) {
                e.status = 'idle';
            }
        },

        async saveBarcode(id) {
            const e = this.editing[id];
            if (!e || !e.value.trim() || e.status === 'taken' || e.status === 'saving') { return; }
            e.status = 'saving';
            try {
                const r = await fetch(`/dashboard/etiquetas/${id}/assign`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ barcode: e.value }),
                });
                if (r.ok) {
                    const p = this.allProducts.find(p => p.id === id);
                    if (p) { p.barcode = e.value; }
                    delete this.editing[id];
                } else {
                    const d = await r.json();
                    e.status  = 'taken';
                    e.message = d.error || 'Error al guardar';
                }
            } catch (err) {
                e.status = 'idle';
            }
        },

        previewStyle() {
            const sizes = { '40x25': [120, 75], '58x40': [160, 110], '62x30': [170, 90] };
            if (this.printMode === 'label') {
                const [w, h] = sizes[this.labelSize] || [120, 75];
                return `width:${w}px; height:${h}px;`;
            }
            const labelW = Math.floor((210 - 2 * this.marginMm - (this.columns - 1) * this.spacingMm) / this.columns * 2.5);
            return `width:${Math.min(labelW, 200)}px; min-height:60px;`;
        },

        submitPrint() {
            const items = this.selectedProductsWithBarcode;
            if (!items.length) { return; }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('dashboard.labels.print') }}';
            form.target = '_blank';

            const addInput = (name, value) => {
                const i = document.createElement('input');
                i.type = 'hidden'; i.name = name; i.value = value;
                form.appendChild(i);
            };

            addInput('_token', document.querySelector('meta[name=csrf-token]').content);
            items.forEach(p => addInput('product_ids[]', p.id));
            addInput('print_mode',          this.printMode);
            addInput('copies',              this.copies);
            addInput('columns',             this.columns);
            addInput('margin_mm',           this.marginMm);
            addInput('spacing_mm',          this.spacingMm);
            addInput('label_size',          this.labelSize);
            addInput('name_font_size',      this.nameFontSize);
            addInput('barcode_height',      this.barcodeHeight);
            addInput('show_barcode_number', this.showBarcodeNumber ? '1' : '0');

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        },

        init() {
            const edits = {};
            this.allProducts.forEach(p => {
                if (!p.barcode) {
                    edits[p.id] = { value: '', status: 'idle', message: '' };
                }
            });
            this.editing = edits;

            this.$watch('search',            () => { this.page = 1; });
            this.$watch('filter',            () => { this.page = 1; });
        },
    };
}
</script>
@endpush
