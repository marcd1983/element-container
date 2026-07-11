<% require css('antlion/element-container:css/element-container.css') %>

<div class="element-container $MarginClasses" style="color:{$TextColor};<% if $BackgroundColor %>background-color: #{$BackgroundColor};<% end_if %><% if $BackgroundImage %>background-image:url('{$BackgroundImage.URL}');background-position: center;background-size: cover;background-repeat: no-repeat;<% end_if %>">
  <% if $HasOverlay %><div style="background-color:{$OverlayRGBA}"><% end_if %>
    <div class="grid-container $ContainerWidthClass">
      <div class="grid-x grid-padding-x grid-padding-y $PaddingClasses">
          $InnerElements
      </div>
    </div>
  <% if $HasOverlay %></div><% end_if %>
</div>
