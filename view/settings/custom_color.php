<div id="rw_custom_color_container">
    <div id="rw_custom_color" style="display: none;">
    <h4>Setting a custom image for your rating</h4>
    <div class="rw-content">
        <p>
        You can override the image of the star by setting a custom image.
        The Rating-Widget use only one image for all states of the star.
        You must create your image the same size and dimensions that you selected.<br />
        If you look at the image for the blue star below you will see how they are laid out.
        </p>
        <center id="rw_stars_preview">
            <i class="rw-blue"></i>
            <span style="left: 189px;">1</span>
            <span style="left: 209px;">2</span>
            <span style="left: 229px;">3</span>
            <span style="left: 249px;">4</span>
            <span style="left: 269px;">5</span>
            <span style="left: 289px;">6</span>
        </center>
        <br />
        <p>
            The stars above are all in one image.
            <ul>
                <li>Stars 2 + 3 used for displaying the rating for the hover mode. i.e. when a person moves their mouse over the Rating-Widget.</li>
                <li>Star 1 used for displaying empty star.</li>
                <li>Star 3 used for displaying full star.</li>
                <li>Star 4 used for displaying Left-To-Right half star.</li>
                <li>Star 5 used for displaying Right-To-Left half star.</li>
            </ul> 
        </p>
        Lets see some example of heart custom style images:
        <br />
        <table cellspacing="0">
            <tr><td>small (16px x 96px)</td><td><i class="rw-heart rw-small"></i></td></tr>
            <tr><td>medium (20px x 120px)</td><td><i class="rw-heart rw-medium"></i></td></tr>
            <tr><td>large (30px x 180px)</td><td><i class="rw-heart rw-large"></i></td></tr>
        </table>
        <br />
        <p><b>Specify your own image to use as a rating.</b></p>
        <br />
        <label for="rw_custom_url">Image URL:</label>
        <input id="rw_custom_url" style="width: 258px;" type="text" value="" />
    </div>
    <div style="text-align: right; background: rgb(240,240,240); padding: 10px;">
        <input type="button" value="Cancel" onclick="RWM.cancelCustom();" />
        <input type="button" value="Update" style="font-weight: bold;" onclick="RWM.setCustom();" />
    </div>
    </div>
</div>
