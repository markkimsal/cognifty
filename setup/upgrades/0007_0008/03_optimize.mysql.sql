ALTER TABLE `cgn_blog_comment`
    MODIFY `source` char(10) NOT NULL DEFAULT 'comment';

ALTER TABLE `cgn_mxq_channel`
    MODIFY `channel_type` char(10) NOT NULL DEFAULT 'point';

ALTER TABLE `cgn_site_struct`
    MODIFY `node_kind` char(10) NOT NULL DEFAULT 'web';
