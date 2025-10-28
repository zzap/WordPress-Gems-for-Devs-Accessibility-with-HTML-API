/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {Element} Element to render.
 */
export default function save() {
    return (
        <div {...useBlockProps.save()}>
            <form>
                <p>
                    <input name="name" type="text" placeholder="Enter your name" />
                </p>
                <p>
                    <label>Email</label>
                    <input name="email" type="email" />
                </p>
                <div>
                    <label>Select an option:</label>
                    <select id="options-id" name="options">
                        <option value="1">Option 1</option>
                        <option value="2">Option 2</option>
                    </select>
                </div>
                <button>Submit</button>
            </form>
        </div>
    );
}
