<form class="ewo-form ewo-installation-form" autocomplete="off">
  <div class="ewo-row">
    <!-- Columna izquierda: Property Details -->
    <div class="ewo-col-2">
      <h2 class="ewo-section-title">Property Details</h2>
      <div class="ewo-row">
        <div class="ewo-col-1">
          <label for="building_color">Building Color<span class="ewo-required">*</span></label>
          <input type="text" id="building_color" name="building_color" placeholder="Type Here..." required>
        </div>
      </div>
      <div class="ewo-row">
        <div class="ewo-col-1">
          <label for="roof_type">Roof Type<span class="ewo-required">*</span></label>
          <select id="roof_type" name="roof_type" required>
            <option value="">Choose Option</option>
            <option value="metal">Metal</option>
            <option value="shingles">Shingles</option>
            <option value="clay_shingles">Clay Shingles</option>
          </select>
        </div>
      </div>
      <div class="ewo-row">
        <div class="ewo-col-1">
          <label for="house_style">Style<span class="ewo-required">*</span></label>
          <select id="house_style" name="house_style" required>
            <option value="">Choose Option</option>
            <option value="one_story_house">One Story House</option>
            <option value="two_story_house">Two Story House</option>
            <option value="mobile_home">Mobile Home</option>
            <option value="town_house">Town House</option>
            <option value="one_story_apartment_building">One Story Apartment Building</option>
            <option value="two_story_apartment_building">Two Story Apartment Building</option>
            <option value="three_story_apartment_building">Three Story Apartment Building</option>
          </select>
        </div>
      </div>
      <div class="ewo-row">
        <div class="ewo-col-1 rent-own-toggle">
          <label>Rent/Own<span class="ewo-required">*</span></label>
          <div class="ewo-toggle-group">
            <button type="button" class="ewo-toggle-btn active" data-target="rent_own" data-value="rent">Rent</button>
            <button type="button" class="ewo-toggle-btn" data-target="rent_own" data-value="own">Own</button>
            <input type="hidden" name="rent_own" id="rent_own" value="rent">
          </div>
        </div>
      </div>
      <div class="ewo-row">
        <div class="ewo-col-1">
          <label for="property_additional_info">Please provide any additional information technicians should know before coming to your home.</label>
          <textarea id="property_additional_info" name="property_additional_info" placeholder="Type Here..." rows="3"></textarea>
        </div>
      </div>
      <div class="ewo-row">
        <div class="ewo-col-1">
          <small style="color:#555; font-size:14px;">If you have pets that may become agitated, please secure them in a separate area to ensure the safety of our technicians. Thank you for your cooperation.</small>
        </div>
      </div>
    </div>
    <!-- Columna derecha: Schedule a Date -->
    <div class="ewo-col-2">
      <h2 class="ewo-section-title">Schedule a Date</h2>
      <div class="ewo-row">
        <div class="ewo-col-1" style="display:flex; gap:1rem;">
          <button type="button" class="ewo-toggle-btn" data-target="schedule_type" data-value="self">Self Schedule</button>
          <button type="button" class="ewo-toggle-btn active" data-target="schedule_type" data-value="call">Have Someone Call Me</button>
          <input type="hidden" name="schedule_type" id="schedule_type" value="call">
        </div>
      </div>
      <div class="ewo-row">
        <div class="ewo-col-1">
          <p style="font-size:15px; color:#222; margin-bottom:10px;">What days are you available for our technicians to install service? Please provide three options. Your date will be confirmed by our support team.</p>
        </div>
      </div>
      <!-- Opciones de fecha -->
      <div class="ewo-row">
        <div class="ewo-col-1">
          <label>Option 1:</label>
          <input type="date" name="install_date_1" required>
          <div style="display:flex; gap:1rem; margin-top:0.5rem;">
            <button type="button" class="ewo-toggle-btn active" data-target="install_time_1" data-value="morning">Morning</button>
            <button type="button" class="ewo-toggle-btn" data-target="install_time_1" data-value="afternoon">Afternoon</button>
            <button type="button" class="ewo-toggle-btn" data-target="install_time_1" data-value="all_day">All Day</button>
            <input type="hidden" name="install_time_1" value="morning">
          </div>
        </div>
      </div>
      <div class="ewo-row">
        <div class="ewo-col-1">
          <label>Option 2:</label>
          <input type="date" name="install_date_2" required>
          <div style="display:flex; gap:1rem; margin-top:0.5rem;">
            <button type="button" class="ewo-toggle-btn active" data-target="install_time_2" data-value="morning">Morning</button>
            <button type="button" class="ewo-toggle-btn" data-target="install_time_2" data-value="afternoon">Afternoon</button>
            <button type="button" class="ewo-toggle-btn" data-target="install_time_2" data-value="all_day">All Day</button>
            <input type="hidden" name="install_time_2" value="morning">
          </div>
        </div>
      </div>
      <div class="ewo-row">
        <div class="ewo-col-1">
          <label>Option 3:</label>
          <input type="date" name="install_date_3" required>
          <div style="display:flex; gap:1rem; margin-top:0.5rem;">
            <button type="button" class="ewo-toggle-btn active" data-target="install_time_3" data-value="morning">Morning</button>
            <button type="button" class="ewo-toggle-btn" data-target="install_time_3" data-value="afternoon">Afternoon</button>
            <button type="button" class="ewo-toggle-btn" data-target="install_time_3" data-value="all_day">All Day</button>
            <input type="hidden" name="install_time_3" value="morning">
          </div>
        </div>
      </div>
      <div class="ewo-row">
        <div class="ewo-col-1">
          <button type="submit" class="ewo-checkout-btn">Proceed to Checkout</button>
        </div>
      </div>
    </div>
  </div>
</form> 