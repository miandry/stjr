$(document).ready(function () {


  // Evenement.lineTabs();

  // Menu.toogle();
  // Menu.showTextMenu();

  // File.upload();

  // //   if($('body').hasClass('gestion')){

  // Car.active();
  // // Car.dynamicHeader(cars);
  // // Car.dynamicContent(cars);
  // Car.dynamic(DataCar, personalCar);
  // Car.generatePersonalCar(personalCar, DataCar);
  // Car.carousel();

  // Personnel.autoGenerate(personal);
  // Personnel.addManual(DataCar, personalCar);
  // Personnel.autoDistribution(personal, DataCar, personalCar);
   Personnel.addIfChecked(personal, DataCar, personalCar);
   Personnel.autoGenerate(personal);
  // Personnel.saveDataCar(personalCar, DataCar);
  //   }
});

const FUNCTION = {
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
  }
};




const Evenement = {
  lineTabs: function () {
    $("#line-tabs").on("click", "button", function () {
      $this = $(this);
      $btns = $("#line-tabs li button");
      $btns.removeClass("active-link");

      $this.addClass("active-link");

      //creer une nouvelle lien voir les detail
      $btns.find(".view-detail").remove();
      const $link = $("<a>", {
        href: "#",
        text: "Voir le detail >>",
        class: "view-detail",
      });
      $this.append($link);
    });
  },
};

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



  addIfChecked: function (dataPersonnel, cars, personalCar) {
    $table = $("#table-personnel");
    $table.on("uncheck.bs.table", function (e, row, $element) {
      const index = $("#table-personnel")
      .bootstrapTable("getData")
      .indexOf(row);
      Templante.default(car, place);
    
    });

    $table.on("check.bs.table", function (e, row, $element) {
      const index = $("#table-personnel")
        .bootstrapTable("getData")
        .indexOf(row);
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
        carSelectOptions += `<option value="${car.id}">${car.name}</option>`;
      }
      for (let i = 1; i <= 22; i++) {
        placeSelectOptions += `<option value="${i}">place ${i}</option>`;
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
              if (place == "" || carId == "") {
                $.alert({
                  title: "Erreur",
                  content:
                    "Selectionnez d'abord le voiture et la place correspondant",
                  type: "red",
                });
              } else {
                const placeWrapper = $(
                  `div#Ligne-${carIdentify}-${place}`
                );
                placeWrapper.html(
                  Templante.newElementPeronnel(personnelInsert,carIdentify,place)
                );
                personalCar.push({
                  car: carIdentify,
                  place: place,
                  personal: personnelInsert,
                });
                //$table.bootstrapTable("uncheck", index);
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
  active: function () {
    $("body").on("click", ".card-car", function () {
      $this = $(this);
      const content = $this.parent().data("bs-target");

      $(content).addClass("show active");
      $(".card-car").removeClass("active-car");

      $this.addClass("active-car");
    });
  },

  dynamic: function (DataCar) {
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
                  <p class="itinaire">${car.start} -  ${car.end}</p>
              </div>
          </button>

      </div>`);
    });

    //content
    DataCar.forEach((car, index) => {
      var showContent = index == 0 ? "show active" : "";
      const identify = `${car.name.split(" ").join("-")}-${car.id}`;
      var PlaceDefault = "";
      for (let i = 1; i <= 22; i++) {
        const place = `p${i}`;
        PlaceDefault += Templante.default(car, place);
      }

      $carContent.append(`
        <!-- Contenu Voiture 1 -->
        <div class="tab-pane fade ${showContent} car-content" id="v-pills-${identify}" role="tabpanel" aria-labelledby="v-pills-${identify}-tab">
            <div class="row" id="${identify}" data-car-id="${car.id}">
              ${Templante.defaultDriver(car, (place = "c"))}
              ${PlaceDefault}
            </div>
        </div>  
      `);
    });
  },

};

const Templante = {
  default: function (car, place) {
    const html = `
      <div class="col-12 col-md-3 mb-3 card-add-personel " 
           data-id-block="${place}" data-car-id="${car.id}">
          
          <div class=" card rounded-4 p-3 w-100 d-flex justify-content-center align-items-center border-2 bg-transparent"
            style="min-height: 140px;  border-style:dashed;">
            <i class="bi me-2 bi-person fs-3 opacity-50"></i>
            <h4 class="m-0 mb-2 p-0 opacity-50"></h4>
            <span>Place ${place} </span>
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

  newElementPeronnel: function (newElement,carIdentify,place) {
    console.log(newElement);
    const html = `
      <div class="card rounded-4 p-3 w-100 bg-white" style="min-height: 140px;">
          <input id="person-placed" type="hidden" name="place-full" value="${carIdentify}#####${place}" />                                        
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
  defaultDriver: function (car, place = "c") {
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
