<div class="space-y-6">
    <div class="space-y-1">
        <flux:heading size="xl">{{ __('Mission Registration') }}</flux:heading>
        <flux:subheading>{{ __('Register for') }}: <span class="font-medium">{{ $mission->title }}</span></flux:subheading>
    </div>

    <form wire:submit.prevent="save" class="space-y-6">
        <!-- Name Section -->
        <flux:fieldset>
            <flux:legend>{{ __('Full Name') }}</flux:legend>
            <div class="grid gap-4 sm:grid-cols-3">
                <flux:input
                    wire:model="first_name"
                    :label="__('First Name')"
                    :error="$errors->first('first_name')"
                    placeholder="{{ __('e.g., Juan')" }}
                    required
                />

                <div class="sm:col-span-2 grid gap-4 sm:grid-cols-2">
                    <flux:input
                        wire:model="middle_initial"
                        :label="__('Middle Initial')"
                        :error="$errors->first('middle_initial')"
                        placeholder="{{ __('e.g., A')" }}
                        maxlength="1"
                    />

                    <flux:select
                        wire:model="suffix"
                        :label="__('Suffix')"
                        :error="$errors->first('suffix')"
                        placeholder="{{ __('Suffix') }}"
                    >
                        @foreach ($this->getSuffixOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:input
                    wire:model="last_name"
                    :label="__('Last Name')"
                    :error="$errors->first('last_name')"
                    placeholder="{{ __('e.g., Dela Cruz')" }}
                    required
                />
            </div>
        </flux:fieldset>

        <!-- Office Selection -->
        <flux:select
            wire:model.live="office"
            :label="__('Office')"
            :error="$errors->first('office')"
            placeholder="{{ __('Select office') }}"
            required
        >
            @foreach ($this->getOfficeOptions() as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </flux:select>

        <!-- Management Tiers (conditional - only if Office is PRDP) -->
        @if ($office === \App\Enums\MissionOffice::PRDP->value)
            <flux:select
                wire:model.live="management_tier"
                :label="__('Management Tier')"
                :error="$errors->first('management_tier')"
                placeholder="{{ __('Select management tier') }}"
            >
                @foreach ($this->getManagementTierOptions() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </flux:select>

            <!-- PSO Cluster (conditional - only if Management Tier is PSO) -->
            @if ($management_tier === \App\Enums\MissionManagementTier::PSO->value)
                <flux:select
                    wire:model="pso_cluster"
                    :label="__('PSO Cluster')"
                    :error="$errors->first('pso_cluster')"
                    placeholder="{{ __('Select PSO cluster') }}"
                    required
                >
                    @foreach ($this->getPsoClusterOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </flux:select>
            @endif

            <!-- RPCO Office (conditional - only if Management Tier is RPCO) -->
            @if ($management_tier === \App\Enums\MissionManagementTier::RPCO->value)
                <flux:select
                    wire:model="rpco_office"
                    :label="__('RPCO Office')"
                    :error="$errors->first('rpco_office')"
                    placeholder="{{ __('Select RPCO office') }}"
                    required
                >
                    @foreach ($this->getRpcoOfficeOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </flux:select>
            @endif

            <!-- Component/Unit (conditional - only if Office is PRDP) -->
            <flux:select
                wire:model="component_unit"
                :label="__('Component / Unit')"
                :error="$errors->first('component_unit')"
                placeholder="{{ __('Select component/unit') }}"
                required
            >
                @foreach ($this->getComponentUnitOptions() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </flux:select>
        @endif

        <!-- Position -->
        <flux:input
            wire:model="position"
            :label="__('Position')"
            :error="$errors->first('position')"
            placeholder="{{ __('e.g., Project Manager') }}"
            required
        />

        <!-- Contact Number and Sex -->
        <div class="grid gap-4 sm:grid-cols-2">
            <flux:input
                wire:model="contact_number"
                :label="__('Contact Number')"
                :error="$errors->first('contact_number')"
                placeholder="{{ __('e.g., 09123456789') }}"
                required
            />

            <flux:input
                wire:model="email"
                :label="__('Email Address')"
                :error="$errors->first('email')"
                placeholder="{{ __('e.g., juan.delacruz@example.com') }}"
            />

            <flux:select
                wire:model="sex"
                :label="__('Sex')"
                :error="$errors->first('sex')"
                placeholder="{{ __('Select sex') }}"
                required
            >
                @foreach ($this->getSexOptions() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </flux:select>
        </div>

        <!-- Data Privacy Consent -->
        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
            {{ __('By filling-up this form you agree with the Data Privacy Policy of the Department of Agriculture - Philippine Rural Development Project.') }}
            {{ __('Both personal and non-personal information may be collected from you for using this form. Rest assured that these data shall be kept safe and secured, and will not be shared to anyone except to designated personnel who will process the needed information only for facilitating smooth participation and distribution of materials for the virtual sessions. The collective information derived from this exercise will be useful for the improvement on implementing similar activities in the future.') }}
        </flux:text>

        <!-- Submit -->
        <div class="flex justify-end gap-2 border-t border-zinc-200 dark:border-zinc-700 pt-5">
            <flux:button
                variant="primary"
                type="submit"
                wire:loading.attr="disabled"
                wire:target="save">
                <span wire:loading.remove wire:target="save">{{ __('Submit Registration') }}</span>
                <span wire:loading wire:target="save">{{ __('Submitting...') }}</span>
            </flux:button>
        </div>
    </form>
</div>
