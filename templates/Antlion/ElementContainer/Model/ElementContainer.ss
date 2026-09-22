<% require css('antlion/element-container:css/element-container.css') %>

<div class="$MarginClasses $ThemeClass" style="<% if $BackgroundColor %>background-color: #{$BackgroundColor};<% end_if %><% if $BackgroundImage %>background-image:url('{$BackgroundImage.URL}');<% with $BackgroundImage.Convert('webp') %>background-image:image-set(url('{$URL}') type('image/webp'), url('{$Up.BackgroundImage.URL}') type('{$Up.BackgroundImage.MimeType}'));<% end_with %>background-position: center;background-size: cover;background-repeat: no-repeat;background-attachment:{$BgAttachmentClass};<% end_if %>">
  <% if $HasOverlay %><div style="background-color:{$OverlayRGBA}"><% end_if %>
    <div class="grid-container $ContainerWidthClass">
      <div class="grid-x $PaddingClasses">
          $InnerElements
      </div>
    </div>
  <% if $HasOverlay %></div><% end_if %>
</div>
