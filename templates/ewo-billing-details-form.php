<?php
// Billing Details Form Template
?>
<form id="ewo-billing-details-form" class="ewo-checkout-form" autocomplete="off">
  <h2>Billing Details</h2>
  <div class="ewo-row">
    <div class="ewo-col-2">
      <label for="first_name">First Name<span class="ewo-required">*</span></label>
      <input type="text" id="first_name" name="first_name" placeholder="Type Here...">
    </div>
    <div class="ewo-col-2">
      <label for="last_name">Last Name<span class="ewo-required">*</span></label>
      <input type="text" id="last_name" name="last_name" placeholder="Type Here...">
    </div>
  </div>
  <div class="ewo-row">
    <div class="ewo-col-2">
      <label for="email">Email Address<span class="ewo-required">*</span></label>
      <input type="email" id="email" name="email" placeholder="Type Here...">
    </div>
    <div class="ewo-col-2">
      <label for="mobile_number">Mobile Number<span class="ewo-required">*</span></label>
      <input type="tel" id="mobile_number" name="mobile_number" placeholder="Type Here...">
    </div>
  </div>
  <div class="ewo-row">
    <div class="ewo-col-2">
      <label for="ad_source">How did you hear about us?<span class="ewo-required">*</span></label>
      <select id="ad_source" name="ad_source">
        <option value="">Choose Option</option>
        <option value="online_ad">Online Ad</option>
        <option value="google_bing_search">Google/Bing Search</option>
        <option value="mailer">Mailer</option>
        <option value="referral">Referral</option>
        <option value="local_event">Local Event</option>
        <option value="tv_radio_theater">TV/Radio/Theater</option>
        <option value="billboard_signage">Billboard/Signage</option>
        <option value="door_hanger_flyer">Door Hanger/Flyer</option>
        <option value="salesperson">Salesperson</option>
      </select>
    </div>
    <div class="ewo-col-2">
      <label for="referral_code">Referral Code</label>
      <input type="text" id="referral_code" name="referral_code" placeholder="Type Here...">
    </div>
  </div>
  <div class="ewo-row">
    <div class="ewo-col-1">
      <label for="address_line_one">Address Line 1<span class="ewo-required">*</span></label>
      <input type="text" id="address_line_one" name="address_line_one" placeholder="Type Here...">
    </div>
  </div>
  <div class="ewo-row">
    <div class="ewo-col-1">
      <label for="address_line_two">Address Line 2</label>
      <input type="text" id="address_line_two" name="address_line_two" placeholder="Type Here...">
    </div>
  </div>
  <div class="ewo-row">
    <div class="ewo-col-1">
      <label for="city">City<span class="ewo-required">*</span></label>
      <input type="text" id="city" name="city" placeholder="Type Here...">
    </div>
  </div>
  <div class="ewo-row">
    <div class="ewo-col-2">
      <label for="state">State<span class="ewo-required">*</span></label>
      <input type="text" id="state" name="state" placeholder="Type Here...">
    </div>
    <div class="ewo-col-2">
      <label for="zip">ZIP Code<span class="ewo-required">*</span></label>
      <input type="text" id="zip" name="zip" placeholder="Type Here...">
    </div>
  </div>
  <div class="ewo-row">
    <div class="ewo-col-2">
      <label for="support_pin">PIN Code<span class="ewo-required">*</span></label>
      <input type="text" id="support_pin" name="support_pin" placeholder="4-digit PIN" maxlength="4" pattern="[0-9]{4}" required>
    </div>
    <div class="ewo-col-2">
      <small>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</small>
    </div>
  </div>
  <hr>
  <div class="ewo-row">
    <div class="ewo-col-2">
    <div class="ewo-form-switch">
        <div class="ewo-toggle-flex-row">
          <label for="text_messages_for_operational_alerts">Consent to Receive Operational Alerts via Text Messages</label>
          <div class="ewo-toggle-group">
            <button type="button" class="ewo-toggle-btn" data-target="text_messages_for_operational_alerts" data-value="Yes">Yes</button>
            <button type="button" class="ewo-toggle-btn" data-target="text_messages_for_operational_alerts" data-value="No">No</button>
        <input type="hidden" name="text_messages_for_operational_alerts" id="text_messages_for_operational_alerts" value="Yes">
          </div>
        </div>
        <small>By enabling this option, you authorize Wisper ISP, LLC to send you text messages containing important updates and alerts related to network events that may affect your service (e.g., outages, maintenance, upgrades). Standard messaging rates may apply.</small>
      </div>
    </div>
    <div class="ewo-col-2">
    <div class="ewo-form-switch">
        <div class="ewo-toggle-flex-row">
          <label for="email_messages_for_operational_alerts">Consent to Receive Operational Alerts via Email</label>
          <div class="ewo-toggle-group">
            <button type="button" class="ewo-toggle-btn" data-target="email_messages_for_operational_alerts" data-value="Yes">Yes</button>
            <button type="button" class="ewo-toggle-btn" data-target="email_messages_for_operational_alerts" data-value="No">No</button>
        <input type="hidden" name="email_messages_for_operational_alerts" id="email_messages_for_operational_alerts" value="Yes">
          </div>
        </div>
        <small>By enabling this option, you consent to receive email communications from Wisper ISP, LLC regarding important updates and alerts for network events that may impact your service (e.g., outages, maintenance, upgrades).</small>
      </div>
    </div>
  </div>
  <div class="ewo-row">
    <div class="ewo-col-2">
    <div class="ewo-form-switch">
        <div class="ewo-toggle-flex-row">
          <label for="subscribe_to_text_payments">Text-Based Bill Payments</label>
          <div class="ewo-toggle-group">
            <button type="button" class="ewo-toggle-btn" data-target="subscribe_to_text_payments" data-value="Yes">Yes</button>
            <button type="button" class="ewo-toggle-btn" data-target="subscribe_to_text_payments" data-value="No">No</button>
        <input type="hidden" name="subscribe_to_text_payments" id="subscribe_to_text_payments" value="Yes">
          </div>
        </div>
        <small>By enabling this option, you authorize Wisper ISP, LLC to send text messages related to bill payments. Additional terms and conditions may apply. Message and data rates may apply.</small>
      </div>
    </div>
    <div class="ewo-col-2">
    <div class="ewo-form-switch">
        <div class="ewo-toggle-flex-row">
          <label for="email_messages_for_wisper_news">Subscription to News Emails</label>
          <div class="ewo-toggle-group">
            <button type="button" class="ewo-toggle-btn" data-target="email_messages_for_wisper_news" data-value="Yes">Yes</button>
            <button type="button" class="ewo-toggle-btn" data-target="email_messages_for_wisper_news" data-value="No">No</button>
        <input type="hidden" name="email_messages_for_wisper_news" id="email_messages_for_wisper_news" value="Yes">
          </div>
        </div>
        <small>By enabling this option, you agree to receive our newsletter via email from Wisper ISP, LLC, which may include company news, service updates, promotions, and other relevant information. You may unsubscribe at any time</small>
      </div>
    </div>
  </div>
  <div class="ewo-row">
    <div class="ewo-col-2"></div>
    <div class="ewo-col-2">
      <div class="ewo-form-switch">
        <div class="ewo-toggle-flex-row">
          <label for="agree_terms">I agree to the T&C's</label>
          <div class="ewo-toggle-group">
            <button type="button" class="ewo-toggle-btn" data-target="agree_terms" data-value="Yes">Yes</button>
            <button type="button" class="ewo-toggle-btn" data-target="agree_terms" data-value="No">No</button>
            <input type="hidden" name="agree_terms" id="agree_terms" value="">
          </div>
        </div>
        <span style="font-weight:400; font-size:14px; color:#555;">By selecting Yes, I agree to the Terms & Conditions, Privacy Policy, and consent to receive communications regarding my order and service.</span>
      </div>
    </div>
  </div>
  <div class="ewo-row">
    <div class="ewo-col-1">
      <div class="ewo-basket-total">
        <div class="ewo-basket-total-row">
          <span>Subtotal</span>
          <span id="ewo-subtotal">$00.00</span>
        </div>
        <div class="ewo-basket-total-row">
          <span>TAX</span>
          <span id="ewo-tax">$00.00</span>
        </div>
        <div class="ewo-basket-total-row total">
          <span>Total</span>
          <span id="ewo-total">$00.00</span>
        </div>
      </div>
    </div>
  </div>
  <div class="ewo-row">
    <div class="ewo-col-1">
      <button type="submit" class="ewo-checkout-btn">Checkout</button>
    </div>
  </div>
</form> 