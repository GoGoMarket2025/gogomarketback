<div class="form-group">
    <label class="form-label font-semibold">
        {{ translate('phone_number') }}
        <span class="input-required-icon">*</span>
    </label>
    <input class="form-control text-align-direction phone-input-with-country-picker"
           type="tel" value="{{ old('user_identity') }}"
           placeholder="{{ translate('enter_phone_number') }}">
    <input type="hidden" class="country-picker-phone-number w-50 firebase-phone-number" name="user_identity" readonly>
</div>
