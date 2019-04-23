<?php
    $party_id = $party['party'];
    $join_url = 'https://bytesurf.io/party.php?action=join&p=' . $party['party'];
    $leave_url = 'https://bytesurf.io/party.php?action=leave';
?>
<div id="party-modal" class="modal">
   
    <!-- modal javascript functions -->
    <script>
        function copy_join_link() {
            let txt = document.getElementById("join_url");
            txt.select();
            document.execCommand("copy");
            alert('Party URL has been copied to clipboard.');
        }
        function leave_party() {
            window.location.href = "<?= $leave_url ?>";
        }
    </script>
    <!-- end modal javascript functions -->
    
    <!-- extra modal styling -->
    <style>
        .modal-btn {
            height: 35px;
            width: 100%;
            margin-bottom: 5px;
        }
    </style>
    <!-- end extra modal styling -->
    
    <!-- the modal box content -->
    <div class="modal-content">
        
        <!-- header -->
        <div class="modal-header">
          <span class="modal-close">&times;</span>
          <h2 style="font-weight: 300; margin-bottom: 0;">Party #<?= $party_id ?></h2>
        </div>
        <!-- end header -->
        
        <!-- body -->
        <div class="modal-body">
            <center><p style="margin-top: 10px;">JOIN LINK</p></center>
            <div class="sign__group" style="width: 100%; margin-bottom: 5px;">
                <input type="text" style="width: 100%; text-align: center; background-color: #2b2b31; border-radius: 4px;" class="sign__input" id="join_url" value="<?= $join_url ?>" readonly>
            </div>
            <div style="width: 100%; padding-bottom: 10px;">
				<button class="modal-btn filter__btn" type="button" onclick="copy_join_link()">COPY URL</button>
                <button class="modal-btn filter__btn" type="button" onclick="leave_party()">LEAVE PARTY</button>
            </div>
        </div>
        <!-- end body -->
        
    </div>
    <!-- end modal body -->
    
</div>