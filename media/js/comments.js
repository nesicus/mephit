function commentForm(thread, parent) {
    div = document.getElementById('comment-' + parent);
    for(var i = 0; i < document.getElementsByName('comment-form').length; i++) {
        document.getElementsByName('comment-form').item(i).innerHTML = '';
    }
    div.innerHTML = div.innerHTML + '<div id="comment-form-"' + parent + '" name="comment-form"><form action="?module=post&action=comment&id=' + parent + '" method="post"><input type="hidden" name="thread" value="' + thread + '" /><input type="hidden" name="parent" value="' + parent + '"/><textarea name="body" cols="50" rows="10"></textarea><br /><input type="submit" name="submit" value="submit"></form></div>';
}
