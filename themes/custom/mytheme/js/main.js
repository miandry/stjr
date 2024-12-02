

$(document).ready(function () {
  Evenement.lineTabs();

  Menu.toogle();
  Menu.showTextMenu();

  File.upload();
 

//   if($('body').hasClass('gestion')){
    
    Car.active();
    // Car.dynamicHeader(cars);
    // Car.dynamicContent(cars);
    Car.dynamic(DataCar,personalCar);
    Car.generatePersonalCar(personalCar, DataCar);
    Car.carousel();
    
    Personnel.autoGenerate(personal);
    Personnel.addManual(DataCar,personalCar);
    Personnel.autoDistribution(personal,DataCar,personalCar);
    Personnel.addIfChecked(personal, DataCar,personalCar);
    Personnel.generateTable(personal);
    Personnel.saveDataCar(personalCar, DataCar);
//   }
});

const Evenement = {
  lineTabs : function() {
    $('#line-tabs').on('click', 'button', function(){
      $this = $(this);
      $btns = $('#line-tabs li button')
      $btns.removeClass('active-link');
      
      $this.addClass('active-link');
      
      //creer une nouvelle lien voir les detail
      $btns.find('.view-detail').remove();
      const $link = $('<a>', {
        href: '#',
        text: 'Voir le detail >>',
        class: 'view-detail'
      });
      $this.append($link);

    })
  }
}

const Menu = {
  toogle: function () {
    $("body").on("click", "#btn-toogle-menu", function () {
      const $menu = $("#menu");
      $menu.toggleClass("d-none");
    });
  },
  showTextMenu: function () {
    $("body").on("mouseover", "#menu", function () {
      $this = $(this);
      var text = $(".toogle-text");
      text.remove("d-none");
    });
    $("body").on("mouseleave", "#menu", function () {
      $this = $(this);
      var text = $(".toogle-text");
      text.add("d-none");
    });
  },
};

const File = {
  upload: function () {
    $("body").on("change", "#file_upload", function () {
      const $this = $(this);
      const fileName = $("#file_name");
      const file = $this.prop("files")[0];

      fileName.val(file.name);

      const reader = new FileReader();
      reader.onload = function (event) {
        const data = new Uint8Array(event.target.result);
        const workbook = XLSX.read(data, { type: "array" });
        // console.log(workbook);

        const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
        const sheetData = XLSX.utils.sheet_to_html(firstSheet);

        $("#xlsxModalLabel").text(file.name);
        $("#fileContent").html(sheetData);
        $("#xlsxModal").modal("show");
      };
      reader.readAsArrayBuffer(file);
    });
  },
};

const Personnel = {
  autoGenerate: function (data) {
    var $tablePersonel = $("#table-personnel");
    var dataTable = [
      //{card : html}
    ];

    data.forEach((element) => {
      const html = `
        <div class="position-relative rounded-4 p-3 mb-2 card-personnel" data-id="${element.id}">
            <h3 class="m-0 mb-2 p-0">${element.name}</h3>
            <p class="opacity-50 mb-4">${element.adresse}</p>
            <span class="d-flex align-items-center gap-2">
                <p class="m-0 text-secondary fw-bold p-2 rounded-3" style="background: #F4F9FD;">${element.point}
                </p>
                <i class="bi bi-arrow-up fs-2 m-2 color-yellow"></i>
            </span>
        </div>
        `;
      
      dataTable.push({ card: html });
      $tablePersonel.bootstrapTable("load", dataTable);
    });
    
  },

  addManual: function (DataCar,personalCar) {
    $("body").on("click", ".card-add-personel", function () {
      $this = $(this);  
      
      
      let newElement = {};
      const target = $this.data("target");
      const $table = $("#table-list-personnel");

      $(target).modal("show");

      $table.on("check.bs.table", function (e, row, $element) {
        newElement = row;

        $(".add-maual-personnel").on("click", function () {
          const car = DataCar.find(item => item.id == $this.data('car-id'));
          const place = $this.data('id-block');

          if ($this.hasClass("is-driver")) {
            $this.html(Templante.newElementDriver(newElement));
          } else {
            $this.html(Templante.newElementPeronnel(newElement));
          }

          personalCar.push({
            car : car,
            place : place,
            personal : newElement,
          });

          $(target).modal("hide");          
          
          const index = $("#table-personnel")
            .bootstrapTable("getData")
            .indexOf(row);
          $table.bootstrapTable("uncheck", index);
        });
      });
    });
  },

  autoDistribution: function (listPersonel, cars, personalCar) {
    var PersonnelSelected = [];
    $("body").on("change","#personel-list input[type='checkbox']", function () {
      var checked = $("#personel-list input[type='checkbox']:checked")      
      $(".checked-lenght").text(checked.length);

      checked.each(function() {
        var dataId = $(this).closest('tr').find('.card-personnel').data('id');
        const personnel = listPersonel.find(item => item.id == dataId);
        if(personnel != undefined){
          PersonnelSelected.push(personnel);
        }
      });      
    });

    $("body").on("click", ".btn-auto-distribution", function () {
      const $this = $(this);
      var carSelectOptions = "";
      let carId = "";
      let carIdentify =""

      for (const car of cars) {
        const identify = `${car.name.split(" ").join("-")}-${car.id}`;
        carSelectOptions += `<option value="#${identify}" data-id="${car.id}">${car.name}</option>`;
      }
      $.confirm({
        title: "Vous voulez ajoutez dans quelle voiture?",
        type: "blue",
        content: `
        <form id="popupForm" class="d-flex justify-content-center align-items-center">
          <div class="form-group border-0 mb-2 form-control">
            <label for="carSelect" class="fw-bold h5">Voiture</label>
            <select id="carSelect" name="option" class="form-control">
              <option value="">Selectionner la voiture</option>
              ${carSelectOptions}
              
            </select>
          </div>

        </form>`,
        onContentReady: function () {
          $("#carSelect").change(function () {
            carIdentify = $(this).val();
            
            carId = $(this).find(":selected").data("id");
          });
        },
        buttons: {
          confirm: {
            text: "oui",
            btnClass: "btn-primary",
            action: function () {
              currentCar = cars.find((item) => item.id === carId);              
              for (const [index,personnel] of PersonnelSelected.entries()) {
                if(index > 21) {
                  break;
                }
                var place =`p${index + 1}`;                
                const placeWrapper = $(`${carIdentify} div[data-id-block="${place}"]`);
                placeWrapper.html(Templante.newElementPeronnel(personnel));

                personalCar.push({
                  car : currentCar,
                  place : place,
                  personal : personnel,
                })
              }              
              $.alert({
                title : "SUCCESS",
                content: "importation reussie",
                autoClose: "cancel|1000",
                buttons: {
                  cancel:{
                    text: "ok",
                    btnClass: "btn-success",
                  }
                }
              })
            },
          },

          cancel: {
            text: "Non",
            btnClass: "btn-secondary",
            action: function () {
             //
            },
          },
        },
      });

    });
  },


  saveDataCar: function(personalCar, DataCar){
    $('body').on('click', '#save-data-car', function() {
      const url = $(this).data('url');
      
      $.ajax({
        url : url,
        type: "GET",
        data: {
          cars : DataCar,
          personalCar: personalCar,
        },
        success: function() {
          $.alert({
            title: "succès",
            content: "Les données sont bien sauvegardée.",
            autoClose: "cancel|2000",
            type: "green",
            buttons:{
              cancel: {
                text: "ok",
                btn : "btn-success",
              }
            }
          })
        },
        error: function() {
          $.alert({
            title: "succès",
            content: "Erreur Serveur",
            autoClose: "cancel|2000",
            type: "red",
            buttons:{
              cancel: {
                text: "ok",
                btn : "btn-danger",
              }
            }
          })
        }
      })
    })
  },

  addIfChecked: function (dataPersonnel, cars, personalCar) {
    $table = $("#table-personnel");

    $table.on("check.bs.table", function (e, row, $element) {
      const index = $("#table-personnel").bootstrapTable("getData").indexOf(row);
      const rowChecked = $element.closest("tr").find("div");
      const personnelInsert = dataPersonnel.find(
        (item) => item.id == rowChecked.data("id")
      );

      let carId = "";
      let place = "";
      let carSelectOptions = "";
      let placeSelectOptions = "";
      let carIdentify = "";

      for (const car of cars) {
        const identify = `${car.name.split(" ").join("-")}-${car.id}`;
        carSelectOptions += `<option value="#${identify}" data-id="${car.id}">${car.name}</option>`;
      }
      for (let i = 1; i <= 22; i++) {
        placeSelectOptions += `<option value="p${i}">place ${i}</option>`;
      }
      $.confirm({
        title: "Vous voulez ajoutez dans quelle voiture?",
        type: "blue",
        content: `
        <form id="popupForm" class="d-flex justify-content-center align-items-center">
          <div class="form-group border-0 mb-2 form-control">
            <label for="carSelect" class="fw-bold h5">Voiture</label>
            <select id="carSelect" name="option" class="form-control">
              <option value="">Selectionner la voiture</option>
              ${carSelectOptions}
              
            </select>
          </div>
          <div class="form-group border-0 form-control">
            <label for="placeSelect" class="fw-bold h5">Place</label>
            <select id="placeSelect" name="option" class="form-control">
              <option value="">Selectioner une place</option>
              ${placeSelectOptions}
            </select>
          </div>
        </form>`,
        onContentReady: function () {
          $("#carSelect").change(function () {
            carIdentify = $(this).val();
            carId = $(this).find(":selected").data("id");
          });

          $("#placeSelect").change(function () {
            place = $(this).find(":selected").val();
          });
        },
        buttons: {
          confirm: {
            text: "oui",
            btnClass: "btn-primary",
            action: function () {
              if(place == "" || carId == ""){
                $.alert({
                  title: "Erreur",
                  content: "Selectionnez d'abord le voiture et la place correspondant",
                  type: "red",
                })
              }else{
                currentCar = cars.find((item) => item.id === carId);
                const placeWrapper = $(`${carIdentify} div[data-id-block="${place}"]`);
                placeWrapper.html(Templante.newElementPeronnel(personnelInsert));
                personalCar.push({
                  car : currentCar,
                  place : place,
                  personal : personnelInsert,
                })         
                $table.bootstrapTable("uncheck", index);
            }

            },
          },
          cancel: {
            text: "Non",
            btnClass: "btn-secondary",
            action: function () {
              $table.bootstrapTable("uncheck", index);
            },
          },
        },
      });

      return cars;
    });
  },

  generateTable: function (data) {
    $(".table-list-personnel").bootstrapTable("load", data);
  },
};

const Car = {
  carousel: function () {
    $("#cars").slick({
      dots: false,
      infinite: false,
      speed: 300,
      slidesToShow: 4,
      slidesToScroll: 4,

      responsive: [
        {
          breakpoint: 1024,
          settings: {
            slidesToShow: 3,
            slidesToScroll: 3,
            infinite: true,
            dots: true,
          },
        },
        {
          breakpoint: 600,
          settings: {
            slidesToShow: 2,
            slidesToScroll: 2,
          },
        },
        {
          breakpoint: 480,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1,
          },
        },
      ],
    });
  },

  active: function () {
    $("body").on("click", ".card-car", function () {
      $this = $(this);
      const content = $this.parent().data("bs-target");

      $(content).addClass("show active");
      $(".card-car").removeClass("active-car");

      $this.addClass("active-car");
    });
  },

  dynamic : function(DataCar) {
    const $cars = $("#cars");
    const $carContent = $("#cars-contents");
    DataCar.forEach((car, index) => {
      var active = "";
      var activeCar = "";
      var selected = false;
      const identify = `${car.name.split(" ").join("-")}-${car.id}`;

      if (index == 0) {
        active = "active";
        selected = true;
        activeCar = "active-car";
      }

      $cars.append(` 
      <!--Voiture ${car.id}-->
      <div class="col-12 col-md-3 mb-3" role="presentation">

          <button class="nav-link bg-white rounded-5 p-1 w-100 ${active}" id="v-pills-${identify}-tab" 
              data-bs-toggle="pill" data-bs-target="#v-pills-${identify}" type="button" 
              role="tab" aria-controls="v-pills-${identify}" aria-selected="${selected}">
              <div class="p-2 px-3 rounded-5 card-car text-dark ${activeCar}"
                  style="background: #F4F9FD;">
                  <h4 class="m-0">${car.name}</h4>  
                  <p>${car.start} -  ${car.end}</p>
              </div>
          </button>

      </div>`);
    });

    //content
    DataCar.forEach((car, index) => {
      var showContent = index == 0 ? "show active" : "";
      const identify = `${car.name.split(" ").join("-")}-${car.id}`;
      var PlaceDefault = '';
      for (let i = 1; i <= 22 ; i++) {
        const place = `p${i}`;
        PlaceDefault += Templante.default(car,place);
      }

      $carContent.append(`
        <!-- Contenu Voiture 1 -->
        <div class="tab-pane fade ${showContent} car-content" id="v-pills-${identify}" role="tabpanel" aria-labelledby="v-pills-${identify}-tab">
            <div class="row" id="${identify}" data-car-id="${car.id}">
              ${Templante.defaultDriver(car,place="c")}
              ${PlaceDefault}
            </div>
        </div>  
      `);
    });
  },

  generatePersonalCar : function(personalCar, DataCar) {
    for (const [index,pc] of personalCar.entries()) {
      
      const place = pc.place;
      const car = pc.car;
      const exists = DataCar.some(c => 
        c.id === car.id &&
        c.name === car.name
      );

      if(exists){
        const carIdentify = `${car.name.split(" ").join("-")}-${car.id}`
        const placeWrapper = $(`#${carIdentify} div[data-id-block="${place}"]`);
        placeWrapper.html(Templante.newElementPeronnel(pc.personal));
      }
    } 
  }
};

const Templante = {
  default: function (car, place) {
    const html = `
      <div class="col-12 col-md-3 mb-3 card-add-personel " data-toggle="modal"
          data-target="#add-personel" data-id-block="${place}" data-car-id="${car.id}">
          
          <div class=" card rounded-4 p-3 w-100 d-flex justify-content-center align-items-center border-2 bg-transparent"
            style="min-height: 140px;  border-style:dashed;">
            <i class="bi me-2 bi-plus-lg fs-3 opacity-50"></i>
            <h4 class="m-0 mb-2 p-0 opacity-50">Ajout mauel</h4>
        </div>

      </div>
    `;
    return html;
  },

  personel: function (data) {
    const html = `
        <div class="col-12 col-md-3 mb-3 card-add-personel" data-toggle="modal"
            data-target="#add-personel" data-id-block=${data.place}>
            
            <div class="card rounded-4 p-3 w-100 bg-white" style="min-height: 140px;">
                <h3 class="m-0 mb-2 p-0">${data.personale.name}</h3>
                <p class="opacity-50">${data.personale.adresse}</p>
                <span class="d-flex align-items-center gap-1">
                    <p class="m-0 text-secondary fw-bold p-2 rounded-3"
                        style="background: #F4F9FD;">${data.personale.point}
                    </p>
                    <i class="bi bi-arrow-up fs-4 color-yellow"></i>
                </span>
            </div>

        </div>`;
    return html;
  },

  newElementPeronnel: function (newElement) {
    const html = `
      <div class="card rounded-4 p-3 w-100 bg-white" style="min-height: 140px;">
          <h3 class="m-0 mb-2 p-0">${newElement.name}</h3>
          <p class="opacity-50">${newElement.adresse}</p>
          <span class="d-flex align-items-center gap-1">
              <p class="m-0 text-secondary fw-bold p-2 rounded-3"
                  style="background: #F4F9FD;">${newElement.point}
              </p>
              <i class="bi bi-arrow-up fs-4 color-yellow"></i>
          </span>
      </div>
    `;
    return html;
  },
  newElementDriver: function (newElement) {
    const html = `
       <div class="card rounded-4 p-3 w-100 d-flex flex-row justify-content-between align-items-center bg-white" style="min-height: 140px;">
            <div class="">
              <h3 class="m-0 mb-2 p-0">${newElement.name}</h3>
              <p class="opacity-50">Chauffeur</p>
              <span class="d-flex align-items-center gap-1">
                  <p class="m-0 text-secondary fw-bold p-2 rounded-3"
                      style="background: #F4F9FD;">${newElement.point}
                  </p>
                  <i class="bi bi-arrow-up fs-4 color-yellow"></i>
                  <i class="bi bi-arrow-down fs-4 color-green"></i>
                </span>
            </div>
            <img src="./assets/image/volant.png" alt="" class="opacity-50"style="width: 100px; height: 100px;">
        </div>
    `;
    return html;
  },
  driver: function (data) {
    const personale = data.personale;

    const html = `
    <div class="col-12 col-md-6 card-add-personel mb-3 bg-white" data-toggle="modal" data-target="#add-personel" data-id-block="${data.place}">
        <div class="card rounded-4 p-3 w-100 d-flex flex-row justify-content-between align-items-center" style="min-height: 140px;">
            <div class="">
              <h3 class="m-0 mb-2 p-0">${personale.name}</h3>
              <p class="opacity-50">Chauffeur</p>
              <span class="d-flex align-items-center gap-1">
                  <p class="m-0 text-secondary fw-bold p-2 rounded-3"
                      style="background: #F4F9FD;">${personale.point}
                  </p>
                  <i class="bi bi-arrow-up fs-4 color-yellow"></i>
                  <i class="bi bi-arrow-down fs-4 color-green"></i>
                </span>
            </div>
            <img src="./assets/image/volant.png" alt="" class="opacity-50"style="width: 100px; height: 100px;">
        </div>
      </div>`;
    return html;
  },
  defaultDriver: function (car,place="c") {
    const html = `
     <div class="col-12 col-md-6 mb-3 card-add-personel is-driver" data-toggle="modal"
          data-target="#add-personel" data-id-block="${place}" data-car-id="${car.id}">
          
          <div class=" card rounded-4 p-3 w-100 d-flex justify-content-center align-items-center border-2 bg-transparent"
            style="min-height: 140px;  border-style:dashed;">
            <i class="bi me-2 bi-plus-lg fs-3 opacity-50"></i>
            <h4 class="m-0 mb-2 p-0 opacity-50">Ajout mauel</h4>
        </div>

      </div>`;

    return html;
  },
};
