@php
    $assignmentsByAttr = collect();
    if ($product->exists && $product->relationLoaded('attributeAssignments')) {
        $assignmentsByAttr = $product->attributeAssignments->keyBy('attribute_id');
    }
@endphp

<div class="space-y-6">
    @foreach ($productAttributes as $ai => $attr)
        @php
            /** @var \App\Models\ProductAttribute $attr */
            $asg = $assignmentsByAttr->get($attr->id);
        @endphp
        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <input type="hidden" name="attributes[{{ $ai }}][attribute_id]" value="{{ $attr->id }}">
            <x-admin.select name="attributes[{{ $ai }}][attribute_value_id]" :label="$attr->name">
                <option value="">{{ __('admin.none') }}</option>
                @foreach ($attr->values as $v)
                    <option value="{{ $v->id }}" @selected(old('attributes.'.$ai.'.attribute_value_id', $asg?->attribute_value_id) == $v->id)>
                        {{ $v->value }}
                    </option>
                @endforeach
            </x-admin.select>
            <div class="mt-3">
                <x-admin.input
                    name="attributes[{{ $ai }}][custom_value]"
                    :label="__('admin.custom_value')"
                    :value="old('attributes.'.$ai.'.custom_value', $asg?->custom_value)"
                />
            </div>
        </div>
    @endforeach
</div>
