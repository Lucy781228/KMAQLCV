<template>
  <div class="comments" v-if="comments">
    <div class="scrollable-list">
      <div v-for="comment in comments" :key="comment.comment_id" class="comment-item">
        <p><strong>{{ comment.user_id }}</strong></p>
        <div class="comment-content">
          <p>{{ comment.message }}</p>
          <ArrowLeftTop :size="16" class="reply-button" @click.stop="setReplyCommentId(comment.comment_id)" />
        </div>
        <div v-for="reply in findReplies(comment.comment_id)" :key="reply.comment_id" class="reply-item">
          <p><strong>{{ reply.user_id }}</strong></p>
          <div class="reply-content">
            <p>{{ reply.message }}</p>
            <ArrowLeftTop :size="16" class="reply-button" v-show="false"/>
          </div>
        </div>
        <div class="reply-form" v-if="replyCommentId === comment.comment_id" v-click-outside="clearReplyCommentId">
          <input class="new-reply" type="text" v-model="reply" placeholder="Trả lời" />
          <NcButton type="tertiary-no-background" @click="createComment" ariaLabel="A" v-if="reply.trim()!=''">
            <template #icon>
              <SendVariant :size="16" />
            </template>
          </NcButton>
        </div>
      </div>
    </div>
    <div class="comment-form">
      <textarea class="new-comment" type="text" v-model="message" placeholder="Bình luận"> </textarea>
      <NcButton type="tertiary-no-background" @click="createComment" ariaLabel="A" v-if="message.trim()!=''">
        <template #icon>
          <SendVariant :size="20" />
        </template>
      </NcButton>
    </div>
  </div>
</template>

<script>
import axios from "@nextcloud/axios";
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { NcButton } from "@nextcloud/vue";
import { getCurrentUser } from '@nextcloud/auth'
import ArrowLeftTop from 'vue-material-design-icons/ArrowLeftTop.vue'
import SendVariant from 'vue-material-design-icons/SendVariant.vue'

export default {
  name: 'Comment',
  components: {
    NcButton,
    ArrowLeftTop,
    SendVariant
  },
  props: {
    workId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      message: '',
      reply: '',
      replyCommentId: 0,
      comments: [],
      replies: [],
      user: getCurrentUser(),
    };
  },
  methods: {
    setReplyCommentId(id) {
      this.replyCommentId = id
    },

    clearReplyCommentId() {
      this.replyCommentId = 0
      this.reply = ''
    },

    async getComments() {
      try {
        const response = await axios.get(generateUrl(`/apps/qlcv/comments/${this.workId}`));
        const commentsData = response.data.comments;

        const comments = [];
        const replies = [];

        commentsData.forEach(comment => {
          if (comment.reply_comment_id === 0) {
            comments.push(comment);
          } else {
            replies.push(comment);
          }
        });

        this.comments = comments
        this.replies = replies
        if(this.replyCommentId===0) this.scrollToBottom()
      } catch (e) {
        console.error(e)
      }
    },

    findReplies(comment_id) {
      return this.replies.filter(reply => reply.reply_comment_id === comment_id);
    },

    async createComment() {
      try {
        const response = await axios.post(generateUrl('/apps/qlcv/create_comment'), {
          message: this.replyCommentId ? this.reply : this.message,
          reply_comment_id: this.replyCommentId,
          user_id: this.user.uid,
          work_id: this.workId
                });
        showSuccess(t('qlcv', 'Thêm thành công'));
        await this.getComments()
        this.message = ''
        this.reply = ''
        this.replyCommentId = 0
      } catch (e) {
        console.error(e)
      }
    },

    scrollToBottom() {
      this.$nextTick(() => {
        const list = this.$el.querySelector('.scrollable-list');
        if (list) {
          list.scrollTop = list.scrollHeight;
        }
      });
    },
  },
  mounted() {
    this.getComments()
    // this.scrollToBottom()
  },
};
</script>

<style scoped>
.comments {
  height: 500px;
  padding: 20px;
}

.comment-item {
  margin-bottom: 10px;
}

.reply-item {
  margin-left: 50px;
}

.comment-form {
  margin-top: 20px;
}

.scrollable-list {
  overflow-y: auto;
  max-height: 350px;
  height: 350px;
  margin-bottom: 20px;
  border: 1px solid #ccc;
  padding: 20px;
  word-wrap: break-word;
}

.comment-form {
  display: flex;
  align-items: center;
  gap: 10px;
}

.reply-form {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-left: 50px
}

.comment-item p {
  margin: 0;
}

.comment-content,
.reply-content {
  display: flex;
  align-items: center;
  padding: 15px;
  background-color: #e5f2f9;
  border: 1px solid #0082c9;
  border-radius: 5px;
  margin-bottom: 20px
}

.comment-content p,
.reply-content p {
  margin-right: 20px;
  user-select: text;
}

.new-comment {
  height: 88px !important;
  width: 100%;
  resize: none;
}

.new-reply {
  width: 100%
}

.reply-button {
  margin-left: auto;
  visibility: hidden
}

.comment-content:hover .reply-button,
.reply-content:hover .reply-button {
  visibility: visible;
  cursor: pointer;
}
</style>
