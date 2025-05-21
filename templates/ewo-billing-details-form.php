<?php
// Billing Details Form Template
?>
<form id="ewo-billing-details-form" autocomplete="off">
  <h2>Billing Details</h2>
  <div class="ewo-form-row">
    <div class="ewo-form-group">
      <label for="first_name">First Name*</label>
      <input type="text" id="first_name" name="first_name" required placeholder="Type Here...">
    </div>
    <div class="ewo-form-group">
      <label for="last_name">Last Name*</label>
      <input type="text" id="last_name" name="last_name" required placeholder="Type Here...">
    </div>
  </div>
  <div class="ewo-form-row">
    <div class="ewo-form-group">
      <label for="email">Email Address*</label>
      <input type="email" id="email" name="email" required placeholder="Type Here...">
    </div>
    <div class="ewo-form-group">
      <label for="mobile_number">Mobile Number*</label>
      <input type="tel" id="mobile_number" name="mobile_number" required placeholder="Type Here...">
    </div>
  </div>
  <div class="ewo-form-row">
    <div class="ewo-form-group">
      <label for="ad_source">How did you hear about us?*</label>
      <select id="ad_source" name="ad_source" required>
        <option value="">Choose Option</option>
        <option value="friend">Friend/Family</option>
        <option value="social">Social Media</option>
        <option value="event">Local Event</option>
        <option value="web">Web Search</option>
        <option value="other">Other</option>
      </select>
    </div>
    <div class="ewo-form-group">
      <label for="referral_code">Referral Code</label>
      <input type="text" id="referral_code" name="referral_code" placeholder="Type Here...">
    </div>
  </div>
  <div class="ewo-form-row">
    <div class="ewo-form-group">
      <label for="address_line_one">Address Line 1*</label>
      <input type="text" id="address_line_one" name="address_line_one" required placeholder="Type Here...">
    </div>
    <div class="ewo-form-group">
      <label for="address_line_two">Address Line 2</label>
      <input type="text" id="address_line_two" name="address_line_two" placeholder="Type Here...">
    </div>
  </div>
  <div class="ewo-form-row">
    <div class="ewo-form-group">
      <label for="city">City*</label>
      <input type="text" id="city" name="city" required placeholder="Type Here...">
    </div>
  </div>
  <div class="ewo-form-row">
    <div class="ewo-form-group">
      <label for="state">State*</label>
      <input type="text" id="state" name="state" required placeholder="Type Here...">
    </div>
    <div class="ewo-form-group">
      <label for="zip">ZIP Code*</label>
      <input type="text" id="zip" name="zip" required placeholder="Type Here...">
    </div>
  </div>
  <div class="ewo-form-row">
    <div class="ewo-form-group">
      <label for="support_pin">Support Pin*</label>
      <input type="text" id="support_pin" name="support_pin" required placeholder="Type Here...">
    </div>
    <div class="ewo-form-group">
      <small>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</small>
    </div>
  </div>
  <hr>
  <div class="ewo-form-row ewo-switch-row">
    <div class="ewo-form-switch">
      <label>Text Messages for Operational Alerts</label>
      <div>
        <button type="button" class="ewo-switch" data-target="text_messages_for_operational_alerts" data-value="Yes">Yes</button>
        <button type="button" class="ewo-switch" data-target="text_messages_for_operational_alerts" data-value="No">No</button>
        <input type="hidden" name="text_messages_for_operational_alerts" id="text_messages_for_operational_alerts" value="Yes">
      </div>
      <small>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</small>
    </div>
    <div class="ewo-form-switch">
      <label>Email Messages for Operational Alerts</label>
      <div>
        <button type="button" class="ewo-switch" data-target="email_messages_for_operational_alerts" data-value="Yes">Yes</button>
        <button type="button" class="ewo-switch" data-target="email_messages_for_operational_alerts" data-value="No">No</button>
        <input type="hidden" name="email_messages_for_operational_alerts" id="email_messages_for_operational_alerts" value="Yes">
      </div>
      <small>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</small>
    </div>
  </div>
  <div class="ewo-form-row ewo-switch-row">
    <div class="ewo-form-switch">
      <label>Subscribe to 'Text Payments'</label>
      <div>
        <button type="button" class="ewo-switch" data-target="subscribe_to_text_payments" data-value="Yes">Yes</button>
        <button type="button" class="ewo-switch" data-target="subscribe_to_text_payments" data-value="No">No</button>
        <input type="hidden" name="subscribe_to_text_payments" id="subscribe_to_text_payments" value="Yes">
      </div>
      <small>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</small>
    </div>
    <div class="ewo-form-switch">
      <label>Email Messages for Wisper News</label>
      <div>
        <button type="button" class="ewo-switch" data-target="email_messages_for_wisper_news" data-value="Yes">Yes</button>
        <button type="button" class="ewo-switch" data-target="email_messages_for_wisper_news" data-value="No">No</button>
        <input type="hidden" name="email_messages_for_wisper_news" id="email_messages_for_wisper_news" value="Yes">
      </div>
      <small>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</small>
    </div>
  </div>
  <div class="ewo-form-row">
    <label class="ewo-checkbox">
      <input type="checkbox" id="agree_terms" name="agree_terms" required>
      I agree to the T&C's
    </label>
  </div>
  <div class="ewo-form-row">
    <button type="submit" class="ewo-submit-btn">Submit</button>
  </div>
</form> 