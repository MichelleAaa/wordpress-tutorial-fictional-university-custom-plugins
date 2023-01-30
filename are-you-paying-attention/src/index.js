wp.blocks.registerBlockType("ourplugin/are-you-paying-attention", {
  title: "Are You Paying Attention?", // This is what people actually see.
  icon: "smiley",
  category: "common",
  // Both the edit and save functions can access these attributes. 
  attributes: {
    skyColor: {type: "string", source: "text", selector: ".skyColor"},
    grassColor: {type: "string", source: "text", selector: ".grassColor"}
  }, 
  // The edit function is a bit different than normal JS. We have to work with PHP which is why the return statement is returning something JS typically wouldn't on it's own, outside of WP.
  // wp.element.createElement("h3", null, "Hello, this is from the admin editor screen.") -- this is the way we return new elements from JS into WP. -- almost no one uses this method though as it's hard to nest. They often use JSX, as below, which looks almost identical to HTML. It allows us to create complex interfaces.
// To use JSX, we just need to install @wordpress/scripts

  edit: function (props) {
    function updateSkyColor(event) {
      props.setAttributes({skyColor: event.target.value})
    }


    function updateGrassColor(event) {
      props.setAttributes({grassColor: event.target.value})
    }
// Below is JSX, react fragment. To use JSX, we just need to install @wordpress/scripts -- ensure you have a package.json and install it. Since we are using JSX, we will need to npm run build and then run the plugin from the build folder for it to work. (First, ensure that node is installed on your computer. You can check with node --version in the terminal.)
// For JSX you can use <> </> or another element like a div.
// In JSX, every element needs to be closed with /> in one way or another. so <input /> instead of <input>

    return (
      <div>
        <input type="text" placeholder="sky color" value={props.attributes.skyColor} onChange={updateSkyColor} />
        <input type="text" placeholder="grass color" value={props.attributes.grassColor} onChange={updateGrassColor} />
      </div>
    )
  },
  save: function (props) {
    return null
  },
  deprecated: [{
    attributes: {
        skyColor: {type: "string", source: "text", selector: ".skyColor"},
        grassColor: {type: "string", source: "text", selector: ".grassColor"}
    },
    save: function (props) {
      return null
    }
  }, {
    attributes: {
      skyColor: {type: "string", source: "text", selector: ".skyColor"},
      grassColor: {type: "string", source: "text", selector: ".grassColor"}
    },
    save: function (props) {
      return null
    }
  }]
})
//In the php file we would list wp.blocks as a dependency. We need it loaded before the JS file.
