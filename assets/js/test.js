async function EnterDatatoDatabase() {
	var name = $("#inputdata").attr('name');
	alert(name);
    await doTheThing(name)
      .then((data) => {
        console.log(data)
		alert(data);
		results = eval(data);
		console.log(results[1]);
        //doSomethingElse()
      })
      .catch((error) => {
        console.log(error)
      })
	console.log('Statement 2');
    console.log('Statement 3');
}


function doTheThing(name) {
  return new Promise((resolve, reject) => {
    $.ajax({
	  url: 'GENERALFunctions.php',
	  type: 'POST',
	  data: {method: 'test', name:name},
      success: function (data) {
        resolve(data)
		console.log('Statement 1');

      },
      error: function (error) {
        reject(error)
      },
    })
  })
}

