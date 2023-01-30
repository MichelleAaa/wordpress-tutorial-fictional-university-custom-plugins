import "./index.scss"
// Note that for scss the system is going to automatically bundle up a css file -- as long as you have npm start running it's going to handle the conversion as you edit.
import { TextControl, Flex, FlexBlock, FlexItem, Button, Icon, PanelBody, PanelRow, ColorPicker } from "@wordpress/components" //The WP solution we are using from WP scripts will see this and find it within the global scope of WP. -- This looks in the web browsers global scope. (So in some cases people will install this but for our setup we don't need to.)
// It requires a dependency to be listed in the php file though.
import { InspectorControls, BlockControls, AlignmentToolbar, useBlockProps } from "@wordpress/block-editor" // we don't need to install this from npm either for our configuration as it as well be looked for in the browser's global scope.
import { ChromePicker } from "react-color"
; // this one has to be installed with npm.

// (function ....)() -- immediately invoked function expression -- this formatting calls the function, so we don't need to include the function call at the bottom. 
(function () {
  let locked = false

  // WP will call the .subscribe function each time data changes on the block editor.
  wp.data.subscribe(function () {
    const results = wp.data
      .select("core/block-editor")
      .getBlocks()
      .filter(function (block) {
        return block.name == "ourplugin/are-you-paying-attention" && block.attributes.correctAnswer == undefined
        //.getBlocks returns an array, which is why we can chain .filter onto it.
      })

    if (results.length && locked == false) { //if it's not already locked, then we want to lock it.
      locked = true
      wp.data.dispatch("core/editor").lockPostSaving("noanswer") //this will actually lock the field, while the above is just to track that it's locked.
    }

    if (!results.length && locked) { // if it's already locked, we unlock it.
      locked = false
      wp.data.dispatch("core/editor").unlockPostSaving("noanswer")
    }
  })
})()

wp.blocks.registerBlockType("ourplugin/are-you-paying-attention", {
  title: "Are You Paying Attention?",
  icon: "smiley",
  category: "common",
  attributes: {
    question: { type: "string" },
    answers: { type: "array", default: [""] },
    correctAnswer: { type: "number", default: undefined },
    // We will use correctAnswer as the index of the correct answer in the array.
    bgColor: { type: "string", default: "#EBEBEB" },
    theAlignment: { type: "string", default: "left" }
  },
  description: "Give your audience a chance to prove their comprehension.", 
  // In wp-admin in the block panel on the side, this text will display under the title of the block.
  example: {
    // This updates the view of the block when you press the + and hover over it in wp-admin. (in a post that you are editing.)
    attributes: {
      question: "What is my name?",
      correctAnswer: 3,
      answers: ["Meowsalot", "Barksalot", "Purrsloud", "Brad"],
      theAlignment: "center",
      bgColor: "#CFE8F1"
    }
  },
  edit: EditComponent,
  save: function (props) {
    return null
  }
})

function EditComponent(props) {
  const blockProps = useBlockProps({
    className: "paying-attention-edit-block",
    style: { backgroundColor: props.attributes.bgColor }
  }) // note that we are using style:, not style=, however, we can use {...blockProps} below to add these properties to an HTML element.

  function updateQuestion(value) {
    props.setAttributes({ question: value })
  }

  function deleteAnswer(indexToDelete) {
    const newAnswers = props.attributes.answers.filter(function (x, index) {
      return index != indexToDelete
    }) // returns true for all except for the one we are getting rid of.
    props.setAttributes({ answers: newAnswers })

    if (indexToDelete == props.attributes.correctAnswer) {
      props.setAttributes({ correctAnswer: undefined })
    } // if we are deleting the answer that is currently set as the correctAnswer, then we set correctAnswer back to being undefined.

  }

  function markAsCorrect(index) {
    props.setAttributes({ correctAnswer: index })
  }

  // The below is JSX. Note that we need to have an opening and closing tag. <></> can be used.
  // Instead of class, use className=.

  return (
    <div {...blockProps}>
      <BlockControls>
        {/* This appears in the inline toolbar above the content -- If you click on the input field the box will appear with some alignment options -- that's when you go into the post and add this block in wp-admin. */}
        <AlignmentToolbar value={props.attributes.theAlignment} onChange={x => props.setAttributes({ theAlignment: x })} />
      </BlockControls>
      <InspectorControls>
        <PanelBody title="Background Color" initialOpen={true}>
          <PanelRow>
            {/* onChangeComplete  */}
            <ChromePicker color={props.attributes.bgColor} onChangeComplete={x => props.setAttributes({ bgColor: x.hex })} disableAlpha={true} />
          </PanelRow>
        </PanelBody>
      </InspectorControls>
      <TextControl label="Question:" value={props.attributes.question} onChange={updateQuestion} style={{ fontSize: "20px" }} />
      <p style={{ fontSize: "13px", margin: "20px 0 8px 0" }}>Answers:</p>
      {props.attributes.answers.map(function (answer, index) {
        return (
          <Flex>
            <FlexBlock>
              <TextControl
                autoFocus={answer == undefined}
                // evalutes as true when we just recently added a new field -- so when you click to add it, the keyboard will already be automatically focused so you can type.
                value={answer}
                // below concat() is going to create a new copy of the array.
                onChange={newValue => {
                  const newAnswers = props.attributes.answers.concat([])
                  newAnswers[index] = newValue
                  props.setAttributes({ answers: newAnswers })
                }}
              />
            </FlexBlock>
            {/* FlexItems take up the smallest amount of space that they need, unlike FlexBlocks. */}
            <FlexItem>
              <Button onClick={() => markAsCorrect(index)}>
                <Icon className="mark-as-correct" icon={props.attributes.correctAnswer == index ? "star-filled" : "star-empty"} />
                {/* in JSX we can't have if statements, only expressions. It has to boil down to a value only, such as a ternary operator. -- For this, if this item's index is the same as correctAnswer, the star will appear filled due to CSS. Otherwise, it will be a hollow star.*/}
              </Button>
            </FlexItem>
            <FlexItem>
              {/* is link will add an underline for styling */}
              <Button isLink className="attention-delete" onClick={() => deleteAnswer(index)}>
                {/* index is the index number of the current answer we are trying to delete. */}
                Delete
              </Button>
            </FlexItem>
          </Flex>
        )
      })}
      <Button
        isPrimary
        onClick={() => {
          props.setAttributes({ answers: props.attributes.answers.concat([undefined]) })
          // The above adds a new empty string at the end. We are using concat to make a copy instead of mutating the original array.
          // This is being used to add another dropdown for another possible answer.
        }}
      >
        Add another answer
      </Button>
    </div>
  )
}
