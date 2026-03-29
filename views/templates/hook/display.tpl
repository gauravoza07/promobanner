{*
 * Frontend template for displaying promotional banners
 * Shows single banner or carousel based on number of banners
 *}

<div class="promobanner-container">
    {if $banners|@count > 1}
        {* Multiple banners - show as carousel *}
        <div class="promobanner-carousel">
            {foreach from=$banners item=banner}
                <div class="promobanner-slide">
                    {if $banner.image}
                        <img src="{$banner.image_url}" alt="{$banner.title|escape:'html':'UTF-8'}">
                    {/if}
                    <div class="promobanner-overlay">
                        <h2>{$banner.title|escape:'html':'UTF-8'}</h2>
                        {if $banner.description}
                            <div class="promobanner-description">{$banner.description nofilter}</div>
                        {/if}
                        {if $banner.cta_text && $banner.cta_link}
                            <a href="{$banner.cta_link|escape:'html':'UTF-8'}" class="promobanner-cta" target="_blank">
                                {$banner.cta_text|escape:'html':'UTF-8'}
                            </a>
                        {/if}
                    </div>
                </div>
            {/foreach}
        </div>
    {else}
        {* Single banner - show static *}
        {assign var=banner value=$banners[0]}
        <div class="promobanner-single">
            {if $banner.image}
                <img src="{$banner.image_url}" alt="{$banner.title|escape:'html':'UTF-8'}">
            {/if}
            <div class="promobanner-overlay">
                <h2>{$banner.title|escape:'html':'UTF-8'}</h2>
                {if $banner.description}
                    <div class="promobanner-description">{$banner.description nofilter}</div>
                {/if}
                {if $banner.cta_text && $banner.cta_link}
                    <a href="{$banner.cta_link|escape:'html':'UTF-8'}" class="promobanner-cta" target="_blank">
                        {$banner.cta_text|escape:'html':'UTF-8'}
                    </a>
                {/if}
            </div>
        </div>
    {/if}
</div>