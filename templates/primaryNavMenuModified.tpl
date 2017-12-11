{**
 * plugins/generic/cemog/templates/primaryNavMenuModified.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Copyright (c) 2017 FU Berlin
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Primary navigation menu list for OMP
 * Bart:
 * horizontale Navigation: "Edition Romiosini" (Über uns) "Aktuelles" (Mitteilungen) "Bücher" (Katalog) "Newsletter" (Newsletter) [derzeit hartcodiert]
 *}
<ul id="navigationPrimary" class="pkp_navigation_primary pkp_nav_list">

	<li{$submenu_class_attr}>
		<a href="{url router=$smarty.const.ROUTE_PAGE page="about"}">
			{translate key="plugins.generic.cem.primnav.about"}
		</a>
		{if $submenu_class_attr}
		<ul>
			<li>
				<a href="{url router=$smarty.const.ROUTE_PAGE page="about"}">
					{translate key="about.aboutThePress"}
				</a>
			</li>
			{if not empty($contextInfo.editorialTeam)}
			<li>
				<a href="{url router=$smarty.const.ROUTE_PAGE page="about" op="editorialTeam"}">
					{translate key="about.editorialTeam"}
				</a>
			</li>
			{/if}
			{if not empty($contextInfo.submissions)}
			<li>
				<a href="{url router=$smarty.const.ROUTE_PAGE page="about" op="submissions"}">
					{translate key="about.submissions"}
				</a>
			</li>
			{/if}
		</ul>
		{/if}
	</li>
	{if $enableAnnouncements}
		<li>
			<a href="{url router=$smarty.const.ROUTE_PAGE page="announcement"}">
				{translate key="plugins.generic.cemog.submission.primnav.announcements"}
			</a>
		</li>
	{/if}

	<li>
		<a href="{url router=$smarty.const.ROUTE_PAGE page="catalog"}">
			{translate key="plugins.generic.cem.primnav.catalog"}
		</a>
	</li>
	
	<li>
		<a href="{url router=$smarty.const.ROUTE_PAGE page="newsletter"}">
			{translate key="plugins.generic.cem.primnav.newsletter"}
		</a>
	</li>	

	{if $contextInfo.editorialTeam || $contextInfo.submissions}
		{assign var="submenu_class_attr" value=" class='has_submenu'"}
	{/if}
</ul>
