$("#loginForm").submit(function(e){
  e.preventDefault();

  $.ajax({
    url: "login_process.php",
    type: "POST",
    data: $(this).serialize(),
    dataType: "json",
    success: function(res){
      if(res.status === "success"){
        window.location.href = "../dashboard/index.php";
      } else {
        $("#loginMsg").html(
          `<div class="alert alert-danger">${res.message}</div>`
        );
      }
    }
  });
});
