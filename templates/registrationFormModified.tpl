{**
 * plugins/generic/cemog/templates/registrationFormModified.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Copyright (c) 2017 FU Berlin
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the basic registration form fields
 *
 * @uses $locale string Locale key to use in the affiliate field
 * @uses $firstName string First name input entry if available
 * @uses $middleName string Middle name input entry if available
 * @uses $lastName string Last name input entry if available
 * @uses $countries array List of country options
 * @uses $country string The selected country if available
 * @uses $email string Email input entry if available
 * @uses $username string Username input entry if available
 *}

<fieldset class="identity">
	<legend>
		{translate key="user.profile"}
	</legend>
	<div class="fields">
		<div class="first_name">
			<label>
				<span class="label">
					{translate key="user.firstName"}
					<span class="required">*</span>
					<span class="pkp_screen_reader">
						{translate key="common.required"}
					</span>
				</span>
				<input type="text" name="firstName" id="firstName" value="{$firstName|escape}" maxlength="40" required>
			</label>
		</div>
		<input type="hidden" name="middleName" value="" maxlength="40">
		<div class="last_name">
			<label>
				<span class="label">
					{translate key="user.lastName"}
					<span class="required">*</span>
					<span class="pkp_screen_reader">
						{translate key="common.required"}
					</span>
				</span>
				<input type="text" name="lastName" id="lastName" value="{$lastName|escape}" maxlength="40" required>
			</label>
		</div>

		<input type="hidden" name="affiliation[{$primaryLocale|escape}]" id="affiliation" value="">
		{**
		<div class="country">
			<label>
				<span class="label">
					{translate key="common.country"}
				</span>
				<select name="country" id="country">
					<option></option>
					{html_options options=$countries selected=$country}
				</select>
			</label>
		</div> **}	
	</div>
</fieldset>

<fieldset class="login">
	<legend>
		{translate key="user.login"}
	</legend>
	<div class="fields">
		<div class="email">
			<label>
				<span class="label">
					{translate key="user.email"}
					<span class="required">*</span>
					<span class="pkp_screen_reader">
						{translate key="common.required"}
					</span>
				</span>
				<input type="text" name="email" id="email" value="{$email|escape}" maxlength="90" required>
			</label>
		</div>
		<div class="username">
			<label>
				<span class="label">
					{translate key="user.username"}
					<span class="required">*</span>
					<span class="pkp_screen_reader">
						{translate key="common.required"}
					</span>
				</span>
				<input type="text" name="username" id="username" value="{$username|escape}" maxlength="32" required>
			</label>
		</div>
		<div class="password">
			<label>
				<span class="label">
					{translate key="user.password"}
					<span class="required">*</span>
					<span class="pkp_screen_reader">
						{translate key="common.required"}
					</span>
				</span>
				<input type="password" name="password" id="password" password="true" maxlength="32" required>
			</label>
		</div>
		<div class="password">
			<label>
				<span class="label">
					{translate key="user.repeatPassword"}
					<span class="required">*</span>
					<span class="pkp_screen_reader">
						{translate key="common.required"}
					</span>
				</span>
				<input type="password" name="password2" id="password2" password="true" maxlength="32" required>
			</label>
		</div>
		<div class="cemogNewsletter">
			<label>
				{if $cemogNewsletter}
					<input type="checkbox" name="cemogNewsletter" id="cemogNewsletter" value="1" checked="checked">
				{else}
					<input type="checkbox" name="cemogNewsletter" id="cemogNewsletter" value="1">				
				{/if}
				{translate key="plugins.generic.cemog.register.newsletter"}
			</label>
		</div>		
		<div class="sendPassword">
			<label>
				{if $sendPassword}
					<input type="checkbox" name="sendPassword" id="sendPassword" value="1" checked="checked">
				{else}
					<input type="checkbox" name="sendPassword" id="sendPassword" value="1">				
				{/if}
				{translate key="plugins.generic.cemog.register.sendPassword"}
			</label>
		</div>
		<div class="cemogTermsOfUse">
			{url|assign:"termsUrl" page="nutzungsbedingungen"}
			<label>		
				<span class="label">			
					{if $cemogTermsOfUse}
						<input type="checkbox" name="cemogTermsOfUse" id="cemogTermsOfUse" value="1" required="1" checked="checked">
					{else}
						<input type="checkbox" name="cemogTermsOfUse" id="cemogTermsOfUse" value="1" required="1" >				
					{/if}	
					<span class="required">*</span>
					<span class="pkp_screen_reader">
						{translate key="common.required"}
					</span>					
					{translate key="plugins.generic.cemog.register.termsOfUse"}			
				</span>			
			</label>
		</div>		
	</div>
</fieldset>



