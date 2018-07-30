<% if $Photos %>
  <ul class="photos">
    <% loop $Photos %>
      <li class="photo">
        <a href="$URL" title="$Title" data-toggle="lightbox" data-gallery="$Up.HTMLID"{$Up.getTitleModeAttribute($Title)}>
          <img src="$ThumbnailURL" alt="$Title" width="$Up.ThumbnailSize" height="$Up.ThumbnailSize">
        </a>
      </li>
    <% end_loop %>
  </ul>
  <div class="link">
    <a href="$FlickrLink" target="_blank" title="$FlickrLinkTitle">
      <img src="$FlickrLogoURL" alt="Flickr" style="width: {$FlickrLogoWidth}px">
    </a>
  </div>
<% else_if $NoDataMessageShown %>
  <% include Alert Type='warning', Text=$NoDataMessage %>
<% end_if %>
